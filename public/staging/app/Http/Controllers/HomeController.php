<?php

namespace Acelle\Http\Controllers;

use Illuminate\Http\Request;
use Acelle\Model\Subscriber;
use Acelle\Model\MailList;
use Carbon\Carbon;
use DB;
class HomeController extends Controller
{

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    { 

        DB::enableQueryLog();
        $subscribers_dates = [];
        $subscribers_count = [];
        event(new \Acelle\Events\UserUpdated($request->user()->customer));

       //dd($request->user()->customer->id);
        $today = \Carbon\Carbon::today()->format('Y-m-d 23:59:59');
        $date = \Carbon\Carbon::today()->subDays(7)->format('Y-m-d H:i:s');
        $arr_dates = $this->getLastNDays(7);
        foreach($arr_dates as $arr_date){
             $subscribers = Subscriber::Join('mail_lists', function ($join) use($request) {
                    $join->on('subscribers.mail_list_id', '=', 'mail_lists.id')
                         ->where('mail_lists.customer_id', '=', $request->user()->customer->id);
                    });

        //$subscribers =  $subscribers->whereBetween('subscribers.created_at',[$date,$today]);
        $subscribers =  $subscribers->groupByRaw('date(subscribers.created_at)');
        $subscribers =  $subscribers->havingRaw('createDate >= ? and createDate <= ?',[$arr_date.' 00:00:00',$arr_date.' 23:59:59']);
        $subscribers =  $subscribers->selectRaw('count(subscribers.id) ,date(subscribers.created_at) as createDate')->get();

          if(count($subscribers)>0){
               
                $subscribersCount = $subscribers[0]['count(subscribers.id)'];
            }else{
               
                 $subscribersCount = 0;
            }
        
        $subscribers_list = array(date('d M ', strtotime($arr_date)) => $subscribersCount);

        $subscribers_data[] = $subscribers_list;
        }

            $subscribeduser = Subscriber::Join('mail_lists', function ($join) use($request) {
                    $join->on('subscribers.mail_list_id', '=', 'mail_lists.id')
                         ->where('mail_lists.customer_id', '=', $request->user()->customer->id)
                         ->where('subscribers.status','=','subscribed');
                    });
        $subscribeduser =  $subscribeduser->selectRaw('id')->count();



         $unsubscribeduser = Subscriber::Join('mail_lists', function ($join) use($request) {
                    $join->on('subscribers.mail_list_id', '=', 'mail_lists.id')
                         ->where('mail_lists.customer_id', '=', $request->user()->customer->id)
                         ->where('subscribers.status','=','unsubscribed');
                    });
        $unsubscribeduser =  $unsubscribeduser->selectRaw('id')->count();

        foreach($subscribers_data as $subscribers){

            $subscribers_dates[] = array_keys($subscribers)[0];
            $subscribers_count[] = array_values($subscribers)[0];
        }
        
     //dd($subscribers_dates,$subscribers_count,$subscribeduser,$unsubscribeduser,DB::getQueryLog());
        return view('dashboard',compact('subscribers_dates','subscribers_count','unsubscribeduser','subscribeduser'));
    }

    function getLastNDays($days, $format = 'Y-m-d'){
    $m = date("m"); $de= date("d"); $y= date("Y");
    $dateArray = array();
        for($i=0; $i<=$days-1; $i++){
            $dateArray[] =  date($format, mktime(0,0,0,$m,($de-$i),$y)) ; 
        }
    return array_reverse($dateArray);
    }
}
