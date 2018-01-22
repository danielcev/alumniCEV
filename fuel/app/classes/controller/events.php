<?php
//require_once '../../../vendor/autoload.php';
use Firebase\JWT\JWT;

class Controller_Events extends Controller_Rest
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
        if (empty($_POST['title']) || empty($_POST['description']) ) 
            {

              return $this->createResponse(400, 'Falta parámetros obligatorios (title, description) ');
            }

        $title = $_POST['title'];
        $description = $_POST['description'];
        try {
            $eventDB = new Model_Events();
            $eventDB->title = $title;
            $eventDB->description = $description;

            if (!empty($_POST['image'])) {
            	$eventDB->image = $_POST['image'];
            }
            if (!empty($_POST['lat'])) {
            	$eventDB->lat = $_POST['lat'];
            }
            if (!empty($_POST['lon'])) {
            	$eventDB->lon = $_POST['lon'];
            }
            if (!empty($_POST['group'])) {
            	$eventDB->group = $_POST['group'];
            }
            $eventDB->id_user = $user->data->id;

            $eventDB->save();
            return $this->createResponse(200, 'Evento creado');

        } catch (Exception $e) 
        {
            return $this->createResponse(500, $e->getMessage());
        }
    }

    function get_events()
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

        $group = $user->data->group;
        $group = '%'.$group.'%';
   
        try {
            
            
            $events = Model_Events::find('all', array(
            'where' => array(
                array('group' ,'LIKE' ,$group),
                ),
            )); 

            $events2 = Model_Events::find('all', array(
            'where' => array(
                array('group' ,null),
                ),
            )); 
            foreach ($events2 as $key => $value) {
                $events[$key] = $value;
            }

            if ($events == null) {
                return $this->createResponse(400, 'No existen eventos');
            }
            return $this->createResponse(200, 'Listado de eventos', $events);

        } catch (Exception $e) 
        {
            return $this->createResponse(500, $e->getMessage());
        }
    }

    function get_event()
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

        if (empty($_GET['id'])) 
        {
          return $this->createResponse(400, 'Falta parámetros obligatorios (id) ');
        }

        $id = $_GET['id'];
   
        try {
            
            $event = Model_Events::find($id); 
            if ($event == null) {
                return $this->createResponse(400, 'No existe el evento');

            }

            // TODO sacar los comentarios y devolverlos --------------------------------------------------------------------------
            $commentsBD = Model_Comments::find('all', array(
                'where' => array(
                array('id_event' ,$id),
                ),
            ));
            return $this->createResponse(200, 'Evento y comentarios', array('event'=>$event , 'comments'=> $commentsBD));

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

        if (empty($_GET['id'])) 
        {
          return $this->createResponse(400, 'Falta parámetros obligatorios (id) ');
        }

        $id = $_GET['id'];
   
        try {
            
            $event = Model_Events::find($id); 
            if ($event == null) {
                return $this->createResponse(400, 'No existe el evento');
            }

            if ($event->id_user == $user->id) {
                $event->delete();
                return $this->createResponse(200, 'Evento borrado');
            }else{

            }

            

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