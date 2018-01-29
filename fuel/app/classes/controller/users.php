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

              return $this->createResponse(400, 'Parámetros incorrectos ( email, password)');
            }

            $email = $_POST['email'];
            $password = $_POST['password'];

            if($this->userNotRegistered($email))
            { 

                $newPrivacity = new Model_Privacity(array('phone' => 0,'localization' => 0));
                $newPrivacity->save();
                $props = array('password' => $password, 'id_privacity' => $newPrivacity->id, 'is_registered' => 1);


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

                return $this->createResponse(400, 'E-mail no valido o ya esta registrado');
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
        if (empty($_POST['email']) || empty($_POST['id_rol']) || empty($_POST['id_group']) || empty($_POST['name'])) {
            return $this->createResponse(400, 'Falta parametro email, id_rol, id_group, name');
        }
        $email = $_POST['email'];
        $id_rol = $_POST['id_rol'];
        $id_group = $_POST['id_group'];
        $name = $_POST['name'];
        $username = explode("@", $email)[0];
        try {
            $rolDB = Model_Roles::find($id_rol);
            if ($rolDB == null) {
                return $this->createResponse(400, 'Rol no valido (1-> admin, 2-> profesor, 3-> alumno)');
            }
            //grupo 
            $groupDB = Model_Groups::find($id_group);
            if ($groupDB == null) {
                return $this->createResponse(400, 'id_group no valido');
            }

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
            $newUser = new Model_Users(array('email' => $email,'is_registered'=> 0, 'id_rol'=>$id_rol,  'name' => $name,'username'=> $username));
            $newUser->save();

            // usuario a grupo
            $belongDB = new Model_Belong();
            $belongDB->id_user = $newUser->id;
            $belongDB->id_group = $groupDB->id;
            $belongDB->save();

            return $this->createResponse(200, 'Usuario insertado con exito');

        } catch (Exception $e) 
        {
            return $this->createResponse(500, $e->getMessage());
        }
    }

    function post_delete()
    {
        // falta token
        if (!isset(apache_request_headers()['Authorization']))
        {
            return $this->createResponse(400,'Token no encontrado');
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
        if (empty($_POST['id'])) {
            return $this->createResponse(400, 'Falta parametro id');
        }

        $id = $_POST['id'];

        try {
            // validar que no exista ese usuario en la bbdd
            $userDB = Model_Users::find($id);
            if ($userDB == null) 
            {
                return $this->createResponse(400, 'El usuario no existe');
            }
            $userDB->delete();
            //return $this->createResponse(400, $userDB);
            return $this->createResponse(200, 'Usuario borrado');

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

    function post_update()
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
        // falta parametro 
        if (empty($_POST['id']) ) {
            return $this->createResponse(400, 'Falta parametro id');
        }
        $id = $_POST['id'];
        //admin modifica todos y el usuario el suyo propio
        $user = $this->decodeToken();
        if ($user->data->id_rol != 1 && $user->data->id != $id) {
            return $this->createResponse(401, 'No autorizado');
        }

        
        try {
            
            $userBD = Model_Users::find($id);
            if ($userBD == null) {
                return $this->createResponse(400, 'No existe el usuario');
            }

            if (!empty($_POST['email']) ) {
                $userBD->email = $_POST['email'];
            }
            if (!empty($_POST['phone']) ) {
                $userBD->phone = $_POST['phone'];
            }
            if (!empty($_POST['birthday']) ) {
                $userBD->birthday = $_POST['birthday'];
            }
            if (!empty($_POST['description']) ) {
                $userBD->description = $_POST['description'];
            }
            if (!empty($_POST['photo']) ) {
                $userBD->photo = $_POST['photo'];
            }
            if (!empty($_POST['id_rol']) ) {
                $rolDB = Model_Roles::find($_POST['id_rol']);
                if ($rolDB == null) {
                    return $this->createResponse(200, 'Rol no valido');
                }
                $userBD->id_rol = $_POST['id_rol'];
            }
            $userBD->save();
            return $this->createResponse(200, 'Usuario actualizado');

        } catch (Exception $e) 
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

          //$users = Model_Users::find('all');
          $users = Model_Users::query()->related('roles')->get();

          foreach ($users as $keyUsers => $user) 
          {
              foreach ($user->roles as $keyRoles => $value) {
                  $users[$keyUsers]['rol'] = $value->type;
                  unset($users[$keyUsers]['roles']);
              }
              
          }
          return $this->createResponse(200, 'Usuarios devueltos', Arr::reindex($users));
          exit;   
    }

    function get_user()
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

        $user = $this->decodeToken();
        
        if (empty($_GET['username'])) 
        {
          return $this->createResponse(400, 'Falta parámetros obligatorios (username) ');
        }

        $username = $_GET['username'];

        try {
            
            
            $usersBD = Model_Users::find('all', array(
            'where' => array(
                array('username' ,'LIKE' ,'%'.$username.'%'),
                ),
            )); 

            if ($usersBD == null) {
                return $this->createResponse(400, 'No existe el usuario');
            }
            return $this->createResponse(200, 'Listado de usuarios', $usersBD);

        } catch (Exception $e) 
        {
            return $this->createResponse(500, $e->getMessage());
        }
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