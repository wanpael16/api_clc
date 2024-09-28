<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Laravel\Sanctum\PersonalAccessToken;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;



class AuthController extends Controller
{
    public function register(Request $request)
    {

        //validación de los datos
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed'
        ]);
        //alta del usuario
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();
        //respuesta
        // return response()->json([
        //     "message" => "Alta exitosa"
        // ]);
        return response($user, Response::HTTP_CREATED);
    }

    public function isTokenExpired($tokenId)
    {
        // Obtener el modelo del token usando el token dado
        $tokenModel =  PersonalAccessToken::where('tokenable_id', $tokenId)->first();
        if ($tokenModel) {
            // Comprobar si la fecha de expiración está establecida y si ha pasado
            return   $tokenModel->expires_at ? $tokenModel->expires_at <  $tokenModel->created_at : true;
        }

        return true; // Si no se encuentra el token, consideramos que ha expirado
    }

    public function login(Request $request)
    {

        $json = json_decode(file_get_contents('php://input'), true);
        if (!is_array($json)) {
            $array =
                array(
                    'response' => array(
                        'estado' => 'Bad Request',
                        'mensaje' => 'La peticion HTTP no trae datos para procesar '
                    )
                );
            return response()->json($array,  Response::HTTP_BAD_REQUEST);
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);


        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            // Obtener la fecha actual
            $today = Carbon::now()->format('Y-m-d');
            // Verificar si ya tiene un token creado hoy
            $existingToken = PersonalAccessToken::where('tokenable_id', $user->id)
                ->where('tokenable_type', 'App\Models\User')
                ->whereDate('created_at', $today) // Verificar la fecha de creación
                ->first();
                if ($existingToken) {
                    $token_id =$existingToken->tokenable_id; // obtenemos el tokenable_id.
                if ( $this->isTokenExpired($token_id)) {
                    return response()->json(['error' => 'Token ha expirado'], 401);
                }
                $cookie = cookie('cookie_token', $existingToken->textTokenpPain, 60 * 24);
                return response(["message" => "Credenciales correctas", "token" => $existingToken->textTokenpPain], Response::HTTP_OK)->withoutCookie($cookie);
            } else {
                $token  = $user->createToken('token', ['*'], now()->addDay());
                $tokenModel = $token->accessToken; // Obtiene el modelo del token
                $tokenModel->textTokenpPain = $token->plainTextToken; // Asigna el valor que necesites
                $tokenModel->save(); // Guarda los cambios
                $cookie = cookie('cookie_token', $token->plainTextToken, 60 * 24);
                return response(["token" => $token->plainTextToken], Response::HTTP_OK)->withoutCookie($cookie);
            }
        } else {
            return response(["message" => "Credenciales inválidas"], Response::HTTP_UNAUTHORIZED);
            
        }
    }

    public function userProfile(Request $request)
    {
        if (!Cookie::has('cookie_token')) {
            return response()->json(['message' => 'No estás autenticado.'], 401);
        }
        return response()->json([
            "message" => "userProfile OK",
            "userData" => auth()->user()
        ], Response::HTTP_OK);
    }

    public function logout()
    {   
        $cookie = Cookie::forget('cookie_token');
        return response(["message" => "Cierre de sesión OK"], Response::HTTP_OK)->withCookie($cookie);
    }

    public function allUsers()
    {
        $users = User::all();
        return response()->json([
            "users" => $users
        ]);
    }
}
