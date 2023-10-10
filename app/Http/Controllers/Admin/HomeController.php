<?php

namespace Acelle\Http\Controllers\Admin;

use Acelle\Http\Controllers\Controller;
use Acelle\Model\Notification;
use Acelle\Model\Subscriber;
use Acelle\Model\Automation2;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        parent::__construct();
        
        // Trigger admin monitoring events when admin is logged in
        event(new \Acelle\Events\AdminLoggedIn());
    }

    /**
     * Show the application admin dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    
        $notifications = Notification::top();
        return view('admin.dashboard', [
            'notifications' => $notifications,
            'subscribersCount' => Subscriber::count(),
            'automationsCount' => Automation2::count(),
        ]);
    }
}
