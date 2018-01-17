<?php
//require_once '../../../vendor/autoload.php';
use Firebase\JWT\JWT;

class Controller_Users extends Controller_Rest
{
    private $key = 'my_secret_key';
    protected $format = 'json';

    function post_create()
    {

        try {

            if (empty($_POST['email']) || empty($_POST['password']) ) 
            {

              return $this->createResponse(400, 'Parámetros incorrectos');
            }

            $email = $_POST['email'];
            $password = $_POST['password'];

            if($this->userNotRegistered($email))
            { 
                $username = explode("@", $email)[0];

                if (substr_count($email, "_") > 0)
                { //Alumnos
                    $rol = 3;

                    $arrayAlumno = explode("_", $username);
                    $group = $arrayAlumno[count($arrayAlumno) - 1];
                    
                    if (count($arrayAlumno) == 3)
                    { //Nombre simple
                        //return $this->createResponse(200,$name);
                        $name = $arrayAlumno[0]." ".$arrayAlumno[1];

                    }
                    else
                    { //Nombre compuesto
                        $name = $arrayAlumno[0]." ".$arrayAlumno[1]." ".$arrayAlumno[2];
                    }

                }
                else
                { //Profesores
                    $rol = 2;
                    $name = $username;
                    $group = 'profesor';

                }

                $newPrivacity = new Model_Privacity(array('phone' => 0,'localization' => 0));
                $newPrivacity->save();
                $props = array('name' => $name, 'password' => $password, 'id_rol' => $rol, 'group' => $group, 'username' => $username, 'id_privacity' => $newPrivacity->id, 'is_registered' => 1);


                $newUser = Model_Users::find('first', array(
                   'where' => array(
                       array('email', $email)

                       ),
                   ));

                $newUser->set($props);
                $newUser->save();

                return $this->createResponse(200, 'Usuario creado', ['user' => $newUser]);

            }
            else
            { //Si el email no es valido ( no esta en la bbdd o ya esta registrado )

                return $this->createResponse(400, 'E-mail no válido');
            } 

        }
        catch (Exception $e) 
        {
            return $this->createResponse(500, $e->getMessage());

        }      
    }

    function post_insertUser()
    {
        // falta token
        if (!isset(apache_request_headers()['Authorization']))
        {
            return $this->createResponse(400, 'Token no encontrado');
        }
        $jwt = apache_request_headers()['Authorization'];
        // valdiar token
        try {

            $this->validateToken($jwt);
        } catch (Exception $e) {

            return $this->createResponse(400, 'Error de autentificacion');
        }
        // validar rol de admin
        $user = $this->decodeToken();
        if ($user->data->id_rol != 1) {
            return $this->createResponse(401, 'No autorizado');
        }
        // falta parametro email
        if (empty($_POST['email'])) {
            return $this->createResponse(400, 'Falta parametro email ');
        }
        $email = $_POST['email'];
        try {
            // validar que no exista ese email en la bbdd
            $userDB = Model_Users::find('first', array(
               'where' => array(
                   array('email', $email),
                   ),
            ));
            if ($userDB != null) 
            {
                return $this->createResponse(400, 'El email ya existe');
            }
            // crear un nueov usuario
            $newUser = new Model_Users(array('email' => $email,'is_registered'=> 0));
            $newUser->save();
            return $this->createResponse(200, 'Usuario insertado con exito');

        } catch (Exception $e) 
        {
            return $this->createResponse(500, $e->getMessage());
        }
    }

    function get_login()
    {

        if (empty($_GET['email']) || empty($_GET['password']) )
        {
            return $this->createResponse(400, 'Parámetros incorrectos');
        }

        $email = $_GET['email'];
        $password = $_GET['password'];

        $userDB = Model_Users::find('first', array(
           'where' => array(
               array('email', $email),
               array('password', $password)
               ),
           ));

      	if($userDB != null){ //Si el usuario se ha logueado (existe en la BD)

            if ($userDB['id_rol'] != 1) {
                return $this->createResponse(401, 'No autorizado');
            }
      		//Creación de token
      		$time = time();
      		$token = array(
                    'iat' => $time, 
                    'data' => [ 
                    'id' => $userDB['id'],
                    'email' => $email,
                    'username' => $userDB['username'],
                    'password' => $password,
                    'id_rol' => $userDB['id_rol'],
                    'id_privacity' => $userDB['id_privacity'],
                    'group' => $userDB['group']
                    ]
                );

      		$jwt = JWT::encode($token, $this->key);

            return $this->createResponse(200, 'login correcto', ['token' => $jwt, 'user' => $email]);

        }else{

          return $this->createResponse(400, 'Usuario o contraseña incorrectas');

      }
    }

    function post_login()
    {
        try {

            if (empty($_POST['email']) || empty($_POST['password']) )
            {
                return $this->createResponse(400, 'Parámetros incorrectos');
            }
            $email = $_POST['email'];
            $password = $_POST['password'];

            

            $userDB = Model_Users::find('first', array(
               'where' => array(
                   array('email', $email),
                   array('password', $password)
                   ),
               ));

            if($userDB != null){ //Si el usuario se ha logueado (existe en la BD)

                // si manda coordenadas se guardan
                if (!empty($_POST['lon']) && !empty($_POST['lat']) ) {
                    $lon = $_POST['lon'];
                    $lat = $_POST['lat'];
                    $userDB->lon = $lon;
                    $userDB->lat = $lat;
                    $userDB->save();
                }
                

                //Creación de token
                $time = time();
                $token = array(
                    'iat' => $time, 
                    'data' => [ 
                    'id' => $userDB['id'],
                    'email' => $email,
                    'username' => $userDB['username'],
                    'password' => $password,
                    'id_rol' => $userDB['id_rol'],
                    'id_privacity' => $userDB['id_privacity'],
                    'group' => $userDB['group']
                    ]
                );

                $jwt = JWT::encode($token, $this->key);

                return $this->createResponse(200, 'login correcto', ['token' => $jwt, 'user' => $userDB]);

            }
            else
            {

              return $this->createResponse(400, 'Usuario o contraseña incorrectas');

            }
        } 
        catch (Exception $e) 
        {
            return $this->createResponse(500, $e->getMessage());
        }
    }

    function get_validateEmail()
    {
        if (empty($_GET['email'])) {
            return $this->createResponse(400, 'Faltan parametros');
        }
        $email = $_GET['email'];
        try {

            $userDB = Model_Users::find('first', array(
            'where' => array(
                array('email', $email),
                array('is_registered', 1)
                )
            )); 

            if($userDB != null){
                return $this->createResponse(200, 'Correo valido',array('email'=>$email, 'id'=>$userDB->id) );
            }else{
                return $this->createResponse(400, 'Email no valido');
            }
        } catch (Exception $e) {
            return $this->createResponse(500, $e->getMessage());
        }
    }
    
    function post_recoverPassword()
    {
       if (empty($_POST['id']) || empty($_POST['password']) ) {
            return $this->createResponse(400, 'Faltan parametros');
        } 
        $id = $_POST['id'];
        $password = $_POST['password'];
        try {

            $userDB = Model_Users::find($id); 
            if($userDB != null){
                $userDB->password = $password;
                $userDB->save();
                return $this->createResponse(200, 'Contraseña cambiada',array('Nueva contraseña'=>$password) );
            }else{
                return $this->createResponse(400, 'Usuario no encontrado');
            }
        } catch (Exception $e) {
            return $this->createResponse(500, $e->getMessage());
        }
    }

    function get_allusers()
    {

        // falta token
        if (!isset(apache_request_headers()['Authorization']))
        {
            return $this->createResponse(400, 'Token no encontrado');
        }

        $jwt = apache_request_headers()['Authorization'];

        // validar token
        try {

            $this->validateToken($jwt);
        } catch (Exception $e) {

            return $this->createResponse(400, 'Error de autentificacion');
        }

          $users = Model_Users::find('all');

          $this->createResponse(200, 'Usuarios devueltos', ['users' => $users]);

    }

    function decodeToken()
    {

        $jwt = apache_request_headers()['Authorization'];
        $token = JWT::decode($jwt, $this->key , array('HS256'));
        return $token;
    }

    function userNotRegistered($email)
    {

        $userDB = Model_Users::find('first', array(
            'where' => array(
                array('email', $email),
                array('is_registered', 0)
                )
            )); 

        if($userDB != null){
            return true;
        }else{
            return false;
        }
    }

    function validateToken($jwt)
    {
        $token = JWT::decode($jwt, $this->key, array('HS256'));

        $email = $token->data->email;
        $password = $token->data->password;
        $id = $token->data->id;

        $userDB = Model_Users::find('all', array(
            'where' => array(
                array('email', $email),
                array('password', $password),
                array('id',$id)
                )
            ));
        if($userDB != null){
            return true;
        }else{
            return false;
        }
    }

    function createResponse($code, $message, $data = [])
    {

        $json = $this->response(array(
          'code' => $code,
          'message' => $message,
          'data' => $data
          ));

        return $json;
    }
/*
    function get_user()
    {
        $jwt = apache_request_headers()['Authorization'];

        if($this->validateToken($jwt))
        {

            $id = $_GET['id'];
            $userDB = Model_Users::find($id);

            if($userDB != null)
            {
                $this->createResponse(200, 'Usuario devuelto', ['user' => $userDB]);
            }
            else
            {
                $this->createResponse(500, 'Error en el servidor');
            }

        }
        else
        {
            $this->createResponse(400, 'No tienes permiso para realizar esta acción');
        }
    }


    

    function post_borrar()
    {

        $jwt = apache_request_headers()['Authorization'];

        if($this->validateToken($jwt)){
          $id = $_POST['id'];

          $usuario = Model_Users::find($id);
          $usuario->delete();

          $this->createResponse(200, 'Usuario borrado', ['usuario' => $usuario]);

      }else{

          $this->createResponse(400, 'No tienes permiso para realizar esta acción');

      }
    }

    function post_edit()
    {
        $jwt = apache_request_headers()['Authorization'];

        if($this->validateToken($jwt)){
          $id = $_POST['id'];
          $username = $_POST['username'];
          $password = $_POST['password'];

          $usuario = Model_Users::find($id);
          $usuario->username = $username;
          $usuario->password = $password;
          $usuario->save();

          $this->createResponse(200, 'Usuario editado', ['usuario' => $usuario]);

      }else{

          $this->createResponse(400, 'No tienes permiso para realizar esta acción');

      }
    }
*/
}