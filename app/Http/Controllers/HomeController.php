<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Message;
use Pusher\Pusher;
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Select all users except the one logged in
        $users = User::where('id', '!=', auth()->id())->get();
        
        return view('home',['users'=>$users]);
    }
    public function getMessage($user_id)
    {
       
        $my_id = auth()->id();
        $user_id=(int)$user_id;
     
        // Getting all previous messages for selected user
        // get all sent and recieved messages
        $messages = Message::where(function($query) use ($user_id,$my_id){
            $query->where('from',$my_id)->where('to',$user_id);

        })->orWhere(function($query) use ($user_id,$my_id){
            $query->where('from',$user_id)->where('to',$my_id);

        })->get();
        
        return view('messages.index',['messages'=>$messages]);
    }
    public function sendMessage(Request $request){
        $from = auth()->id();
        $to= $request->receiver_id;
        $message = $request->message;
        $data=new Message;
        $data->from = $from;
        $data->to= $to;
        $data->message = $message;
        $data->is_read = 0;
        $data->save();
        // pusher
        $options = array(
            'cluster' => 'ap2',
            'useTLS' => true
        );

        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            $options
        );

        $data = ['from' => $from, 'to' => $to]; // sending from and to user id when pressed enter
        $pusher->trigger('my-channel', 'my-event', $data);
    }
}
