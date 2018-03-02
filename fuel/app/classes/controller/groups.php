<?php
//require_once '../../../vendor/autoload.php';
use Firebase\JWT\JWT;

class Controller_Groups extends Controller_Rest
{
    private $key = 'my_secret_key';
    protected $format = 'json';


    function post_create()
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
        if (empty($_POST['name']) ) {
            return $this->createResponse(400, 'Falta parametro name');
        }
        $name = $_POST['name'];
        try {

        	
            $groupDB =  Model_Groups::find('all',array(
            	'where'=>array(
            		array('name',$name),
            	),
	        ));
            if ($groupDB != null) {
            	return $this->createResponse(400, 'El grupo ya existe');
            }
            $groupDB = new Model_Groups();
            $groupDB->name= $name;
            $groupDB->save();

            $belong = new Model_Belong(array('id_user' => 1, 'id_group' => $groupDB->id));

            $belong->save();

            return $this->createResponse(200, 'Grupo creado');

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
            $groupDB = Model_Groups::find($id);
            if ($groupDB == null) 
            {
                return $this->createResponse(400, 'El grupo no existe');
            }
            $groupDB->delete();
            return $this->createResponse(200, 'Grupo borrado');

        } catch (Exception $e) 
        {
            return $this->createResponse(500, $e->getMessage());
        }
    }


    function get_groups()
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

          $groups = Model_Groups::find('all');

          $this->createResponse(200, 'Grupos devueltos', Arr::reindex($groups));
    }

    function post_assign()
    {
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
        if (empty($_POST['id_user']) || empty($_POST['id_group'])) {
            return $this->createResponse(400, 'Falta parametro id_user, id_group');
        }

        $id_user = $_POST['id_user'];
		$id_group = $_POST['id_group'];
        try {
            $groupDB = Model_Groups::find($id_group);
            if ($groupDB == null) 
            {
                return $this->createResponse(400, 'El grupo no existe');
            }
            $userDB = Model_Groups::find($id_user);
            if ($userDB == null) 
            {
                return $this->createResponse(400, 'El usuario no existe');
            }
            
            $belongDB = Model_Belong::find('first',array(
            	'where'=>array(
            		array('id_user',$id_user),
            		array('id_group',$id_group),
            	),
	        ));
	        if ($belongDB!=null) {
	        	return $this->createResponse(400, 'Usuario ya esta asignado al grupo');

	        }

            $belongDB = new Model_Belong();
            $belongDB->id_user = $id_user;
            $belongDB->id_group = $id_group;
            $belongDB->save();
            return $this->createResponse(200, 'Usuario asignado a grupo');

        } catch (Exception $e) 
        {
            return $this->createResponse(500, $e->getMessage());
        }
    }
    
    function post_unassign()
    {
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
        if (empty($_POST['id_user']) || empty($_POST['id_group'])) {
            return $this->createResponse(400, 'Falta parametro id_user, id_group');
        }

        $id_user = $_POST['id_user'];
        $id_group = $_POST['id_group'];
        try {
            $groupDB = Model_Groups::find($id_group);
            if ($groupDB == null) 
            {
                return $this->createResponse(400, 'El grupo no existe');
            }
            $userDB = Model_Groups::find($id_user);
            if ($userDB == null) 
            {
                return $this->createResponse(400, 'El usuario no existe');
            }
            
            $belongDB = Model_Belong::find('first',array(
                'where'=>array(
                    array('id_user',$id_user),
                    array('id_group',$id_group),
                ),
            ));
            if ($belongDB==null) {
                return $this->createResponse(400, 'Usuario no pertenece al grupo');

            }
            $belongDB->delete();
            return $this->createResponse(200, 'Usuario desasignado del grupo');

        } catch (Exception $e) 
        {
            return $this->createResponse(500, $e->getMessage());
        }
    }


    //-----
    function decodeToken()
    {

        $jwt = apache_request_headers()['Authorization'];
        $token = JWT::decode($jwt, $this->key , array('HS256'));
        return $token;
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

}