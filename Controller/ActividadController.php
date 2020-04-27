<?php

namespace Controller;

require("./TableGateways/ActividadGateway.php");

use TableGateways\ActividadGateway;

class ActividadController
{

    private $db;
    private $requestMethod;
    private $userId;

    private $actividadGateway;

    public function __construct($db, $requestMethod, $userId)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->userId = $userId;
        $this->actividadGateway = new ActividadGateway($db);
    }

    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'GET':
                if ($this->userId) {
                    $response = $this->get($this->userId);
                } else {
                    $response = $this->getAll();
                };
                break;
            case 'POST':
                $response = $this->create();
                break;
            case 'PUT':
                $response = $this->update($this->userId);
                break;
            case 'DELETE':
                $response = $this->delete($this->userId);
                break;
            default:
                $response = $this->notFoundResponse();
                break;
        }
        header($response['status_code_header']);
        if ($response['data']) {
            //echo $response['data'];
            echo json_encode($response, 200);
        }
    }

    private function getAll()
    {
        $result = $this->actividadGateway->findAll();
        $response['success'] = true;
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['data'] = $result;
        // $response['body'] = json_encode($result);


        //return response()->json($response, 200);

        return $response;
    }

    private function get($id)
    {
        $result = $this->actividadGateway->find($id);
        if (!$result) {
            return $this->notFoundResponse();
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function create()
    {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (!$this->validate($input)) {
            return $this->unprocessableEntityResponse();
        }

        $data = $this->actividadGateway->insert($this->getQuery($input));

        if ($data != 0) {
            $this->actividadGateway->insert($this->getQueryActividad($input)) . "\n";
        } else {
        }

        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['code'] = 201;
        $response['data'] = $data;
        return $response;
    }

    private function update($id)
    {
        $result = $this->actividadGateway->find($id);
        if (!$result) {
            return $this->notFoundResponse();
        }
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (!$this->validate($input)) {
            return $this->unprocessableEntityResponse();
        }
        $this->actividadGateway->update($id, $input);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = null;
        return $response;
    }

    private function delete($id)
    {
        $result = $this->actividadGateway->find($id);
        if (!$result) {
            return $this->notFoundResponse();
        }
        $this->actividadGateway->delete($id);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = null;
        return $response;
    }

    private function validate($input)
    {
        if (!isset($input['entity'])) {
            return false;
        }
        return true;
    }

    private function getQueryActividad($input)
    {
        //$str="";
        $intro = "INSERT INTO actividad (usuario, device, tipo, entity, entity_id, entity_json, sincronizacion, created, updated)";
        $values = "VALUES('" . $input['usuario'] . "','" . $input['device'] . "','" . $input['tipo'] . "','" . $input['entity'] . "', '" . $input['entityId'] . "', '" . $input['entityJson'] . "', '" . $input['sincronizacion'] . "', '" . $input['created'] . "', '" . $input['updated'] . "');";

        return $intro . $values;
    }

    private function getQuery($input)
    {
        $this->getValues($input);
        $str = "";
        switch ($input['entity']) {
            case 'Item':
                $str = "INSERT INTO item(name, info, created, updated) " . $this->getValues($input);

                //  $str = "INSERT INTO item ". $this->getValues($input);
                break;
            default:
                $str = "Error";
                break;
        }
        return $str;
    }

    private function getValues($input)
    {
        //$duplicatekey = " ON DUPLICATE KEY UPDATE id = VALUES(id) + 2";
        //echo "entityJson ".$input['entityJson']."\n";
        $var = (array) json_decode($input['entityJson']);
        $str = "";
        switch ($input['entity']) {
            case 'Item':
                $str = "VALUES('" . $var['name'] . "', '" . $var['info'] . "', '" . $var['created'] . "', '" . $var['updated'] . "');";
                break;
            default:
                $str = "Error";
                break;
        }
        return $str;
    }

    private function unprocessableEntityResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $response['body'] = json_encode([
            'error' => 'Invalid input'
        ]);
        return $response;
    }

    private function notFoundResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found X';
        $response['body'] = null;
        return $response;
    }
}
