<?php
namespace App\Http\Controllers;


use App\Http\Requests;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class EventosController extends Controller implements MessageComponentInterface
{
    protected $clients, $subscriptions, $users;

    public function __construct() {
        $this->subscriptions = [];
        $this->users = [];
        $this->middleware('auth');
        $this->clients = new \SplObjectStorage;
        $this->middleware("modConfiguracion");
        $this->middleware("terminosCondiciones");
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);
        $this->users[$conn->resourceId] = $conn;



       // var_dump($this->clients);
        echo "New connection! With User: ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
       // dd($msg);


        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        $posts = json_decode(stripslashes($msg),true);

        if(isset($posts["action"]) && $posts["action"]=='connect'){
            //$this->users[$from->resourceId] = $posts["roomId"];
            $this->subscriptions[$from->resourceId] = $posts["roomId"];
        }else{
            if (isset($this->subscriptions[$from->resourceId])) {
                $target = $this->subscriptions[$from->resourceId];
                foreach ($this->subscriptions as $id=>$channel) {
                    if ($channel == $target && $id != $from->resourceId) {
                        $this->users[$id]->send($msg);
                        echo "Enviado a ".$from->resourceId." Canal: $target \n";
                    }
                }
            }
        }


    }



    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}
