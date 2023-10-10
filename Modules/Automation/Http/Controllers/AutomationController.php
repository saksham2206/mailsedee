<?php

namespace Modules\Automation\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Acelle\Model\Automation2;
use Acelle\Model\AutomationList;
use Acelle\Model\Template;
use Acelle\Model\Email;
use Acelle\Model\Field;
use Acelle\Model\AutoTrigger;
use Acelle\Model\Customer;
use Acelle\Model\EmailVerificationServer;
use DB;
use Validator;
use Acelle\Events\MailListSubscription;
use Illuminate\Support\Facades\Session;
use Auth;
use Acelle\Model\GraphMailer;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use Acelle\Imports\SubscriberImport;
use Maatwebsite\Excel\HeadingRowImport;
use Maatwebsite\Excel\Concerns\ToArray;


class AutomationController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        //Automation2::run();

        //\Acelle\Model\SendingServerGmail::find(17);
        
        return view('automation::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(Request $request, $uid)
    {
        // init automation
        $AutomationList = AutomationList::findByUid($uid);
        $automations = Automation2::where('main_id',$AutomationList->id)->get();
        $automation = Automation2::where('main_id',$AutomationList->id)->first();
        $diffAutomation = '';
        $segmentName = 'B';
        foreach($automations as $test){
            if($automation->id != $test->id){
                $diffAutomation = $test->uid;
            }
            
        }
        //$diffAutomation = 
        //$automation->updateCacheInBackground();
        //dd($automation);
        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }
        
        return view('automation::create', [
            'AutomationList' => $AutomationList,
            'automations' => $automations,
            'automation' => $automation,
            'diffAutomation' => $diffAutomation,
            'segmentName' => $segmentName
        ]);
    }

    public function createSegment($uid){

        $automationList = AutomationList::findByUid($uid);
        $checkAutomation = Automation2::where('main_id',$automationList->id)->get();
        $mail_list = \Acelle\Model\MailList::where('id',$automationList->mail_list_id)->first();

        $newId = rand(100000000,999999999);
        $optionsArray = array(
                "init"=> true,
                  "type"=> "datetime",
                  "date"=> toDateString(\Carbon\Carbon::now()),
                  "at" => toTimeString(\Carbon\Carbon::now()->addMinutes(30)),
                  "key" => 'specific-date',
            );

        $data[] = array(
                "id"=>"trigger",
                "title"=> "Autostart on a scheduled date/time",
                "type"=> 'ElementTrigger',
                "child"=> null,
                "options"=> $optionsArray,
                "last_executed"=> null,
                "evaluationResult"=> null,
                
            );

        $automations = Automation2::create([
            'name' => $automationList->name,
            'customer_id' => $automationList->customer_id,
            'mail_list_id' => $automationList->mail_list_id,
            'time_zone' => null,
            'status' => $automationList->status,
            'smtp_server_id' =>$automationList->smtp_server_id,
            'data' => json_encode($data),
            'main_id' => $automationList->id,
        ]);
        //return redirect('automation/changeSegment/'.$automations->uid);
        Session::put('automation',$automations);
        return response()->json([
                'status' => 'success',
                'message' => trans('messages.automation.created.redirecting'),
                'url' => url('automation/changeSegment/').'/'.$automations->uid
            ], 201);

    }

    public function step1Store(Request $request){
        $customer = $request->user()->customer;
        $automationNameField = $request->input('name');
        $from_name = $request->input('from_name');
        $sending_server = $request->input('mail_server');
        if($sending_server != null){
            $sending_server_details = \Acelle\Model\SendingServer::where('id',$sending_server)->first();
            $email = $sending_server_details->default_from_email;
            $contact = \Acelle\Model\Contact::create([
                'email' => $email
            ]);
            $contact->save();
            $contact_id = $contact->id;
        }else{
            $contact = \Acelle\Model\Contact::create([
                'email' => 'test@test.com'
            ]);
            $contact->save();
            $contact_id = $contact->id;
            //$contact_id = 2;
        }
        
        
        


        $customer = $request->user()->customer;
        
        
        
        $list = new \Acelle\Model\MailList();
        $ada = array([
            'customer_id'=> $customer->id,
            'contact_id' => $contact_id, 
        ]);

        //exit;
        $list->fill($ada);
        $list->customer_id = $customer->id;
        $list->contact_id = $contact_id;
        $list->name = $automationNameField;
        $list->from_name = $from_name;

        // var_dump($list);
        // exit;
        $list->save();
        // init automation
        $automation = AutomationList::create([
            'name' => $automationNameField,
            'customer_id' => $customer->id,
            'mail_list_id' => $list->id,
            'time_zone' => null,
            'status' => 'active',
            'smtp_server_id' => $sending_server,
            'status' => Automation2::STATUS_INACTIVE,
            'data' => '[{"title":"Click to choose a trigger","id":"trigger","type":"ElementTrigger","options":{"init":"false", "key": ""}}]',
        ]);

        $automationList = AutomationList::findByUid($automation->uid);
        $checkAutomation = Automation2::where('main_id',$automationList->id)->get();
        $mail_list = \Acelle\Model\MailList::where('id',$automationList->mail_list_id)->first();

        $newId = rand(100000000,999999999);
        $optionsArray = array(
                "init"=> true,
                  "type"=> "datetime",
                  "date"=> toDateString(\Carbon\Carbon::now()),
                  "at" => toTimeString(\Carbon\Carbon::now()->addMinutes(30)),
                  "key" => 'specific-date',
            );

        $data[] = array(
                "id"=>"trigger",
                "title"=> "Autostart on a scheduled date/time",
                "type"=> 'ElementTrigger',
                "child"=> null,
                "options"=> $optionsArray,
                "last_executed"=> null,
                "evaluationResult"=> null,
                
            );

        $automations = Automation2::create([
            'name' => $automationList->name,
            'customer_id' => $automationList->customer_id,
            'mail_list_id' => $automationList->mail_list_id,
            'time_zone' => null,
            'status' => $automationList->status,
            'smtp_server_id' =>$automationList->smtp_server_id,
            'data' => json_encode($data),
            'main_id' => $automationList->id,
        ]);
        //dd($automation);
        
        // authorize
        // if (\Gate::denies('create', $automation)) {
        //     return $this->noMoreItem();
        // }
        Session::put('automation',$automation);
        return response()->json([
                'status' => 'success',
                'message' => trans('messages.automation.created.redirecting'),
                'url' => action('Automation2Controller@edit', ['uid' => $automation->uid])
            ], 201);
    }

    public function Step2($automationUid)
    {

        $AutomationList = AutomationList::findByUid($automationUid);
        $automations = Automation2::where('main_id',$AutomationList->id)->get();
        $automation = Automation2::where('main_id',$AutomationList->id)->first();
        $diffAutomation = '';
        $segmentName = 'B';
        foreach($automations as $test){
            if($automation->id != $test->id){
                $diffAutomation = $test->uid;
            }
            
        }
        //$diffAutomation = 
        //$automation->updateCacheInBackground();
        //dd($automation);
        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }
        
        return view('automation::createste2', [
            'AutomationList' => $AutomationList,
            'automations' => $automations,
            'automation' => $automation,
            'diffAutomation' => $diffAutomation,
            'segmentName' => $segmentName
        ]);
        // $automation = AutomationList::findByUid($automationUid);
        // return view('automation2.import',[
        //     'automation' => $automation,
        // ]);
        
    }

    public function step3View($automationUid){

        $automation = Automation2::findByUid($automationUid);
        $automation_uid = $automationUid;
        return view('automation::createstep3',[
            'automation' => $automation,
            'automation_uid' => $automation_uid
        ]);
    }

    public function Step3(Request $request){
        // $emailField = $request->input('EmailField');
        // $NameField = $request->input('NameField');
        // $keyName = $request->input('keyName');
        // $valueName = $request->input('valueName');
        // $fromName = $request->input('from_name');
        // $wholecsvdata = $request->input('wholecsvdata');
        // $csvToArray = json_decode($wholecsvdata);
        // $allCount = count($csvToArray[0]->data);
        // $csvData = $csvToArray[0]->data;
        // $uniqueCsvData = array();
        // $invalidEmails = [];
        // foreach($csvData as $key=>$value)
        // {
        //     if(!isset($uniqueCsvData[$value->email]))
        //     {
        //         /*check valid email verification using curl and kickbox api*/

        //         $curl = curl_init();
        //         curl_setopt($curl, CURLOPT_URL, "https://api.kickbox.com/v2/verify?email=".$value->email."&apikey=live_0e63428e4add748bfb9d865f19f50d89628c6cbe33af7ae510e0a6305418e59b");
        //         curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //         $output = curl_exec($curl);
        //         curl_close($curl);
        //         $result    = json_decode($output);

        //         if($result->reason != 'invalid_domain' && $result->reason != 'unexpected_error')
        //         {
        //            $uniqueCsvData[$value->email] = $value;
        //         }else
        //         {
        //             $invalidEmails[] = $value->email;
        //         }
        //     }
        // }

        // $uniqueCsvData = array_values($uniqueCsvData);
        // $duplicateCount = $allCount - count($uniqueCsvData);
       
        // $saveData = array();
        // $list = \Acelle\Model\MailList::findOrFail($request->list_id);
        // $emailfields = \Acelle\Model\Field::create([
        //     'mail_list_id' => $list->id,
        //     'type' => 'text',
        //     'label' => 'Email',
        //     'required' => '0',
        //     'tag' => strtoupper($emailField),
        // ]);
        // $emailfields->save();
        // $namefields = \Acelle\Model\Field::create([
        //     'mail_list_id' => $list->id,
        //     'type' => 'text',
        //     'label' => 'First name',
        //     'required' => '0',
        //     'tag' => strtoupper($NameField),

        // ]);
        // $namefields->save();
        // if(strtolower($keyName) == 'phone' || strtolower($keyName) == 'mobile no'){
        //     $phonefields = \Acelle\Model\Field::create([
        //         'mail_list_id' => $list->id,
        //         'type' => 'number',
        //         'label' => 'Phone',
        //         'required' => '0',
        //         'tag' => 'PHONE',

        //     ]);
        //     $phonefields->save();
        // }else{
        //     $phonefields = \Acelle\Model\Field::create([
        //         'mail_list_id' => $list->id,
        //         'type' => 'text',
        //         'label' => $keyName,
        //         'required' => '0',
        //         'tag' => strtoupper($valueName),

        //     ]);
        //     $phonefields->save();
        // }
        // $field = "";
        // foreach($uniqueCsvData as $key=> $value){
        //     $values = (array)$value;
        //     $saveData[$emailField] = $values[$emailField];
        //     $saveData[$NameField] = $values[$NameField];
        //     $saveData[$keyName] = $values[$keyName];
            
        //     //if($emailfields == )
        //     $subscriber = \Acelle\Model\Subscriber::create([
        //         'mail_list_id' => $list->id,
        //         'email' => $values[$emailField],
        //         'status' => 'subscribed',
        //         'from' => 'added',

        //     ]);
        //     $subscriber->save();
        //     $subscriberFeild = \Acelle\Model\SubscriberField::create([
        //         'subscriber_id' => $subscriber->id,
        //         'field_id' => $emailfields->id,
        //         'value' => $values[$emailField],
        //     ]);
        //     $subscriberFeild->save();
        //     $subscriberFeild = \Acelle\Model\SubscriberField::create([
        //         'subscriber_id' => $subscriber->id,
        //         'field_id' => $namefields->id,
        //         'value' => $values[$NameField],
        //     ]);
        //     $subscriberFeild->save();
        //     $subscriberFeild = \Acelle\Model\SubscriberField::create([
        //         'subscriber_id' => $subscriber->id,
        //         'field_id' => $phonefields->id,
        //         'value' => $values[$valueName],
        //     ]);
        //     $subscriberFeild->save();

        // }
        // if($duplicateCount == 0)
        // {
        $automation_uid = $request->input('automation_uid');
        $automation = Automation2::findByUid($automation_uid);
           return redirect('automation/step3/'.$automation->uid)->with( ['automation' => $automation] );
        // }else{
        //     return view('createstep3',['status'=>'success','duplicate_count'=>$duplicateCount,'mail_list_id' => $list->id,'name' => $list->name,'invalid_emails'=>$invalidEmails]);
        // }
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        
        $automationList = AutomationList::findByUid($request->uid);

        if(count($automationList)>0){

        }
        $optionsArray = array(
                "init"=> true,
                  "type"=> "datetime",
                  "date"=> toDateString(\Carbon\Carbon::now()),
                  "at" => toTimeString(\Carbon\Carbon::now()->addMinutes(30)),
                  "key" => 'specific-date',
            );

        $data[] = array(
                "id"=>"trigger",
                "title"=> "Autostart on a scheduled date/time",
                "type"=> 'ElementTrigger',
                "child"=> (int)$request->id[1][0],
                "options"=> $optionsArray,
                "last_executed"=> null,
                "evaluationResult"=> null,
                
            );
        $automation = Automation2::findByUid($request->input('automation_uid'));
            $mail_list = \Acelle\Model\MailList::where('id',$automation->mail_list_id)->first();
             $subscriberData = \Acelle\Model\Subscriber::where('mail_list_id',$mail_list->id)->get();
             $listCount = count($subscriberData)/$request->input('segmentNumber');
             if($listCount>0){
                $finalListCount = round($listCount); 
             }else{
                $finalListCount = 0; 
             }
             $listNumber = 1;
             if($finalListCount == 0 ){
               foreach($subscriberData as $value){
                    $subscriberDatas[$listNumber][] = $value->email;
                    
                } 
             }else{
                foreach($subscriberData as $key=> $value){
                    if($key+1 <= $finalListCount){
                        $subscriberDatas[$listNumber][] = $value->email;
                        
                    }else{
                        $listNumber++;
                        $finalListCount += $finalListCount;
                        $subscriberDatas[$listNumber][] = $value->email;
                    }
                }
             }
             // echo "<pre>";
             // var_dump($subscriberDatas);
             // exit;
            $sending_server_details = \Acelle\Model\SendingServer::where('id',$automation->smtp_server_id)->first();
        for($i = 1; $i<=$request->input('segmentNumber'); $i++){

            for ($j=0; $j <count($request->child[$i]) ; $j++) {
            
            $template = Template::findByUid($request->template_uid[$i][$j]);
           
            
            $email = new Email();
            $email->automation2_id = $automation->id;
            $email->subject = $template->subject;
            $email->from = $sending_server_details->default_from_email;
            $email->from_name = 'test';
            $email->reply_to = $sending_server_details->default_from_email;
            $email->sign_dkim = 1;
            $email->track_open = 1;
            $email->track_click = 1;
            $email->action_id = $request->id[$i][$j];
            $email->plain = $template->content;
            
            $email->template_id = $template->id;
            $email->customer_id = Auth::user()->customer->id;
            $email->save();

                $optionsArray = array(
                    "init"=> "true",
                      "email_uid"=> $email->uid,
                      "template"=> "true",
                      'subscribers' => (count($subscriberDatas) <= $i) ? $subscriberDatas[$i]: '',
                );
                if($j == count($request->child[$i])-1){
                    if($i == $request->input('segmentNumber')){
                        $child = null;
                    
                    }else{
                        $k = $i+1;
                        $child = (int)$request->id[$k][0];
                    }
                }else{
                    $child = (int)$request->id[$i][$j+1];
                }

                $data[] = array(
                    "id"=>(int)$request->id[$i][$j],
                    "title"=> $template->subject,
                    "type"=> 'ElementAction',
                    'segmentNumber' => $i,
                    "child"=> $child,
                    "options"=> $optionsArray,
                    "last_executed"=> null,
                    "evaluationResult"=> null,
                    
                );
            }

            

        }
        // //echo "<pre>";
        // print_r(json_encode($data));
        // exit;
        $automation = Automation2::findByUid($request->input('automation_uid'));
        $automation->data = json_encode($data);
        $automation->save();
        return redirect('automation2/'.$automation->uid.'/edit/');

        var_dump(json_encode($data));
    }

    public function createAutomation(Request $request){
        //return $request->uids;
        $automation = Automation2::findByUid($request->uid);
        $automationList = AutomationList::findOrFail($automation->main_id);
        $checkAutomation = Automation2::where('main_id',$automationList->id)->get();
        $mail_list = \Acelle\Model\MailList::where('id',$automationList->mail_list_id)->first();
        $subscriberData = \Acelle\Model\Subscriber::where('mail_list_id',$mail_list->id)->get();  
        if(count($checkAutomation)>1){
            $listCount = count($subscriberData)/2;
             $finalListCount = round($listCount);
             $listNumber = 1;
             foreach($subscriberData as $key => $value){
                if($key+1 <= $finalListCount){
                    $subscriberDatas[$listNumber][] = $value->email;
                    
                }else{
                    $listNumber++;
                    $finalListCount += $finalListCount;
                    $subscriberDatas[$listNumber][] = $value->email;
                }
                    
                    
                } 
        }else{
             $mail_list = \Acelle\Model\MailList::where('id',$automationList->mail_list_id)->first();
             $subscriberData = \Acelle\Model\Subscriber::where('mail_list_id',$mail_list->id)->get();
             foreach($subscriberData as $value){
                    $subscriberDatas[] = $value->email;
                    
                } 
        }
       
        $newId = rand(100000000,999999999);
        $optionsArray = array(
                "init"=> true,
                  "type"=> "datetime",
                  "date"=> toDateString(\Carbon\Carbon::now()),
                  "at" => toTimeString(\Carbon\Carbon::now()->addMinutes(30)),
                  "key" => 'specific-date',
            );

        $data[] = array(
                "id"=>"trigger",
                "title"=> "Autostart on a scheduled date/time",
                "type"=> 'ElementTrigger',
                "child"=> $newId,
                "options"=> $optionsArray,
                "last_executed"=> null,
                "evaluationResult"=> null,
                
            );

        $automationssss = Automation2::findByUid($request->uid);

        $data = json_decode($automationssss->data,true);
            
        $countData = count($data);
        foreach($data as $key => $datas){
            $newData[] = $datas; 
            if($datas['id'] == $request->elementId){
                $newData[$key]['child'] = $newId;
            }
        }
        
        $automationssss->data = json_encode($newData,true);
        $automationssss->save();
        $data = json_decode($automationssss->data,true);
        //dd($automationssss);
        $sending_server_details = \Acelle\Model\SendingServer::where('id',$automationList->smtp_server_id)->first();

        $template = Template::findByUid($request->templateUid);
           
            
            $email = new Email();
            $email->automation2_id = $automation->id;
            $email->subject = $template->subject;
            $email->from = $sending_server_details->default_from_email;
            $email->from_name = 'test';
            $email->reply_to = $sending_server_details->default_from_email;
            $email->sign_dkim = 1;
            $email->track_open = 1;
            $email->track_click = 1;
            $email->action_id = $newId;
            $email->plain = $template->content;
            
            $email->template_id = $template->id;
            $email->customer_id = Auth::user()->customer->id;
            $email->save();
            if(count($checkAutomation)>1){
                $optionsArray = array(
                    "init"=> "true",
                      "email_uid"=> $email->uid,
                      "template"=> "true",
                      'subscribers' =>  $subscriberDatas[2],
                );
            }else{
                 $optionsArray = array(
                    "init"=> "true",
                      "email_uid"=> $email->uid,
                      "template"=> "true",
                      'subscribers' =>  $subscriberDatas,
                );
            }
                $child = null;

                $data[$countData] = array(
                    "id"=>(int)$newId,
                    "title"=> $template->subject,
                    "type"=> 'ElementAction',
                    'segmentNumber' => 1,
                    "child"=> $child,
                    "options"=> $optionsArray,
                    "last_executed"=> null,
                    "evaluationResult"=> null,
                    
                );
                //dd($data);
                $automation->data = json_encode($data);
                $automation->save();

                $checkAutomation = Automation2::where('main_id',$automationList->id)->get();
                if(count($checkAutomation)>1){
                    $listCount = count($subscriberData)/2;
                    $finalListCount = round($listCount);
                    $listNumber = 1;
                    foreach($subscriberData as $key => $value){
                        if($key+1 <= $finalListCount){
                            $subscriberDatas[$listNumber][] = $value->email;
                            
                        }else{
                            $listNumber++;
                            $finalListCount += $finalListCount;
                            $subscriberDatas[$listNumber][] = $value->email;
                        }
                            
                            
                    } 
                    foreach ($checkAutomation as $keyVal => $value) {
                        $dataSet = json_decode($value->data,true);
                        foreach ($dataSet as $keyVals => $values) {
                            if($keyVals != 0){
                                $dataSet[$keyVals]['options']['subscribers'] = $subscriberDatas[$keyVal+1];
                            }
                            
                        }
                        $value->data = json_encode($dataSet,true);
                        $value->save();
                        var_dump($value->data);

                    }
                    exit;
                }
                return true;



    }

    public function updateAutomation(Request $request){
        $automation = Automation2::findByUid($request->uid);
        $automationList = AutomationList::findOrFail($automation->main_id);
        if($request->type == 'click'){
            $mail_list = \Acelle\Model\MailList::where('id',$automationList->mail_list_id)->first();
             $subscriberData = \Acelle\Model\Subscriber::where('mail_list_id',$mail_list->id)->get();
             foreach($subscriberData as $value){
                    $subscriberDatas[] = $value->email;
                    
                } 
            $newId = rand(100000000,999999999);
            $newWaitId = rand(100000000,999999999);
            
            
            $data = json_decode($automation->data,true);
            
            $countData = count($data) -1;
            foreach($data as $key => $datas){
                $newData[] = $datas; 
                if($datas['id'] == $request->elementId){
                    if($request->clickType == 'Yes'){
                        $newData[$key]['childYes'] = $newWaitId;
                    }else{
                        $newData[$key]['childNo'] = $newWaitId;
                    }
                    
                }
            }
            $email_link = $newData[$countData]['options']['email_uid'];
            //$data[$countData]['child'] = $newId;
            $sending_server_details = \Acelle\Model\SendingServer::where('id',$automationList->smtp_server_id)->first();
            $template = Template::findByUid($request->templateUid);
               
                
                $email = new Email();
                $email->automation2_id = $automation->id;
                $email->subject = $template->subject;
                $email->from = $sending_server_details->default_from_email;
                $email->from_name = 'test';
                $email->reply_to = $sending_server_details->default_from_email;
                $email->sign_dkim = 1;
                $email->track_open = 1;
                $email->track_click = 1;
                $email->action_id = $newId;
                $email->plain = $template->content;
                
                $email->template_id = $template->id;
                $email->customer_id = Auth::user()->customer->id;
                $email->save();
                

            
                    $optionsArray = array(
                        "init"=> "true",
                          "email_uid"=> $email->uid,
                          "template"=> "true",
                          'subscribers' =>  $subscriberDatas,
                    );
                    $child = null;

                    $newData[count($data)] = array(
                        "id"=>(int)$newWaitId,
                        "title"=> $template->subject,
                        "type"=> 'ElementAction',
                        'segmentNumber' => 1,
                        "child"=> $child,
                        "options"=> $optionsArray,
                        "last_executed"=> null,
                        "evaluationResult"=> null,
                        
                    );
                    // var_dump(json_encode($newData,false));
                    // exit;
                    $automation->data = json_encode($newData,true);
                    $automation->save();
        }else{
            $mail_list = \Acelle\Model\MailList::where('id',$automationList->mail_list_id)->first();
             $subscriberData = \Acelle\Model\Subscriber::where('mail_list_id',$mail_list->id)->get();
             foreach($subscriberData as $value){
                    $subscriberDatas[] = $value->email;
                    
                } 
            $newId = rand(100000000,999999999);
            $newWaitId = rand(100000000,999999999);
            
            
            $data = json_decode($automation->data,true);
            
            $countData = count($data) -1;
            foreach($data as $key => $datas){
                $newData[] = $datas; 
                if($datas['id'] == $request->elementId){
                    $newData[$key]['child'] = $newId;
                }
            }
            //$data[$countData]['child'] = $newId;
            $sending_server_details = \Acelle\Model\SendingServer::where('id',$automationList->smtp_server_id)->first();
            $template = Template::findByUid($request->templateUid);
               
                
                $email = new Email();
                $email->automation2_id = $automation->id;
                $email->subject = $template->subject;
                $email->from = $sending_server_details->default_from_email;
                $email->from_name = 'test';
                $email->reply_to = $sending_server_details->default_from_email;
                $email->sign_dkim = 1;
                $email->track_open = 1;
                $email->track_click = 1;
                $email->action_id = $newId;
                $email->plain = $template->content;
                
                $email->template_id = $template->id;
                $email->customer_id = Auth::user()->customer->id;
                $email->save();
                $optionsArray = array(
                        "key"=> "wait",
                          "time"=> '5 days',
                          "email_uid"=> $email->uid,
                          "template"=> "true",
                          'subscribers' =>  $subscriberDatas,
                    );
            $newData[count($data)] = array(
                        "id"=>(int)$newId,
                        "title"=> "Wait for 5 days",
                        "type"=> 'ElementWait',
                        'segmentNumber' => 1,
                        "child"=> $newWaitId,
                        "options"=> $optionsArray,
                        "last_executed"=> null,
                        "evaluationResult"=> null,
                        
                    );
                    $optionsArray = array(
                        "init"=> "true",
                          "email_uid"=> $email->uid,
                          "template"=> "true",
                          'subscribers' =>  $subscriberDatas,
                    );
                    $child = null;

                    $newData[count($data)+1] = array(
                        "id"=>(int)$newWaitId,
                        "title"=> $template->subject,
                        "type"=> 'ElementAction',
                        'segmentNumber' => 1,
                        "child"=> $child,
                        "options"=> $optionsArray,
                        "last_executed"=> null,
                        "evaluationResult"=> null,
                        
                    );
                    //var_dump(json_encode($newData,false));
                    $automation->data = json_encode($newData,true);
                    $automation->save();
        }

        $checkAutomation = Automation2::where('main_id',$automationList->id)->get();
        $mail_list = \Acelle\Model\MailList::where('id',$automationList->mail_list_id)->first();
        $subscriberData = \Acelle\Model\Subscriber::where('mail_list_id',$mail_list->id)->get();
        if(count($checkAutomation)>1){
            $subscriberDatas = array();
            $listCount = count($subscriberData)/2;
            $finalListCount = round($listCount);
            $listNumber = 1;

            foreach($subscriberData as $key => $value){
                if($key+1 <= $finalListCount){
                    $subscriberDatas[$listNumber][] = $value->email;
                    
                }else{
                    $listNumber++;
                    $finalListCount += $finalListCount;
                    $subscriberDatas[$listNumber][] = $value->email;
                }
                    
                    
            } 
            foreach ($checkAutomation as $keyVal => $value) {
                $dataSet = json_decode($value->data,true);
                foreach ($dataSet as $keyVals => $values) {
                    if($keyVals != 0){
                        $dataSet[$keyVals]['options']['subscribers'] = $subscriberDatas[$keyVal+1];
                    }
                    
                }
                $value->data = json_encode($dataSet,true);
                $value->save();
                //var_dump($value->data);

            }
            //exit;
        }
        
                return true;
    }

    public function step4($uid){
        $automation = Automation2::findByUid($uid);
        $rules = $this->triggerRules()['specific-date'];

        return view('automation::step4',[
            'key' => 'specific-date',
            'automation' => $automation,
            'trigger' => $automation->getTrigger(),
            'rules' => $rules,
        ]);
    }

    public function triggerRules()
    {
        return [
            'welcome-new-subscriber' => [],
            'say-happy-birthday' => [
                'options.before' => 'required',
                'options.at' => 'required',
                'options.field' => 'required',
            ],
            'specific-date' => [
                'options.date' => 'required',
                'options.at' => 'required',
            ],
            'say-goodbye-subscriber' => [],
            'api-3-0' => [],
            'subscriber-added-date' => [
                'options.delay' => 'required',
                'options.at' => 'required',
            ],
            'weekly-recurring' => [
                'options.days_of_week' => 'required',
                'options.at' => 'required',
            ],
            'monthly-recurring' => [
                'options.days_of_month' => 'required|array|min:1',
                'options.at' => 'required',
            ],
            'woo-abandoned-cart' => [
                'options.source_uid' => 'required',
            ],
        ];
    }

    public function step4Store(Request $request){


            // init automation
            $automation = Automation2::findByUid($request->uid);
            
            // authorize
            if (\Gate::denies('update', $automation)) {
                return $this->notAuthorized();
            }

            list($validator, $result) = $this->vaidateTrigger($request, $request->options['key']);
                
            // redirect if fails
            if (!$result) {
                return response()->view('automation2.triggerSelectConfirm', [
                    'key' => $request->options['key'],
                    'automation' => $automation,
                    'trigger' => $automation->getTrigger(),
                    'rules' => $this->triggerRules()[$request->options['key']],
                    'errors' => $validator->errors(),
                ], 400);
            }
            
            return redirect('automation');
       
    }

    public function vaidateTrigger($request, $type)
    {
        $valid = true;

        $rules = $this->triggerRules()[$type];

        // make validator
        $validator = Validator::make($request->all(), $rules);

        $valid = $valid && !$validator->fails();
        
        return [$validator,  $valid];
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('automation::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('automation::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }

    public function uploadCsvList(Request $request){
        $fileData = '';
        $extension = pathinfo($_FILES['file']['name'][0], PATHINFO_EXTENSION);
        // $validation = Validator::make($request->file,[
        //   'file'   => 'required|mimes:csv',
        // ]);
       
        
        if($extension == 'csv' || $extension == 'xlsx'){
           

            
            if(isset($_FILES['file']['name'][0]))
            {

              foreach($_FILES['file']['name'] as $keys => $values)
              {
                // $excel = \Excel::import(new SubscriberImport,$_FILES['file']['name'][$keys]);
                // dd($excel);
                $fileName = $_FILES['file']['name'][$keys];
                if(move_uploaded_file($_FILES['file']['tmp_name'][$keys], public_path().'/uploadsCsv/' . $values))
                {
                  //$fileData .= '<img src="/uploadsCsv/'.$values.'" class="thumbnail" />';
                  $query = "INSERT INTO uploads (file_name, upload_time)VALUES('".$fileName."','".date("Y-m-d H:i:s")."')";
                  DB::statement($query);
                  $csvFileName = public_path().'/uploadsCsv/'.$fileName;
                  // var_dump($csvFileName);
                  // exit;
                  $csvDataArray[] = $this->csvToArray($csvFileName);
                  // var_dump($csvDataArray);
                  // exit;
                }
              }
            }
            echo json_encode($csvDataArray);
        }else{
             return json_encode(array('status' => false,'Msg' => 'The format you have tried to upload is not valid. Please upload CSV formatted subscribers list.'));
        
        }
    }

    public function csvToArray($filename = '', $delimiter = ',')
    {

        if (!file_exists($filename) || !is_readable($filename))
            return false;

        $header = null;
        $data = array();
        $excelData = \Excel::toArray(new SubscriberImport,$filename);
        // $excel = (new HeadingRowImport)->toArray($filename);
        // echo "<pre>";
        //  var_dump($excelData);
        //  exit;
        //if (($handle = fopen($filename, 'r')) !== false)
        //{
            foreach($excelData[0] as $key => $row)
            {

                if ($key == 0)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            //fclose($handle);
        //}
        $finalData['header'] = $header;
        $finalData['data'] = $data; 

        return $finalData;
    }
    
    public function importContacts(Request $request){
        $list = \Acelle\Model\MailList::findOrFail($request->input('list_id'));
        $emailField = $request->input('EmailField');
        $NameField = $request->input('NameField');
        $keyName = $request->input('keyName');
        $valueName = $request->input('valueName');
        $CompanyField = $request->input('CompanyField');
        $wholecsvdata = $request->input('wholecsvdata');
        $Email_Validate = $request->input('Email_Validate');
        $customer = Auth::user()->customer;
        $subscription = $customer->subscription;
        //dd($subscription->email_verification);
        $csvToArray = json_decode($wholecsvdata);
        $saveData = array();
        $allCount = count($csvToArray[0]->data);
        $csvData = $csvToArray[0]->data;
        foreach($csvToArray[0]->data as $key=> $value){
            $values = (array)$value;
            $validation = Validator::make($values, [$emailField => 'required|email']);
            if ($validation->fails()) {
                return response()->json(['status'=>false,'error' => $validation->errors()]);
            }
        }
        $uniqueCsvData = array();
        $invalidEmails = [];
        $duplicateContactCount = 0;
        $blacklistCount = 0;
        $notVerifyCount =0;
        $countings = 0;
        foreach($csvData as $key=>$value)
        {
            $dataCheck = \Acelle\Model\Subscriber::where('email',$value->$emailField)->where('mail_list_id',$list->id)->get();
            $checkBlacklist = \Acelle\Model\Blacklist::where('email',$value->$emailField)->get();
            if(!count($dataCheck)>0 ){

                if(!isset($uniqueCsvData[$value->$emailField]) && $Email_Validate == 1 && $subscription->email_verification > 0 )
                {

                    $server = EmailVerificationServer::where('status','active')->where('type','emaillistverify.com')->first();
                    if(!empty($server)){
                        $optionData = json_decode($server->options,true);
                        $email = $value->$emailField;
                        $key = $optionData['api_key'];
                        $url = "https://apps.emaillistverify.com/api/verifyEmail?secret=".$key."&email=".$email;
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true );
                        $response = curl_exec($ch);
                        //echo $response;
                        curl_close($ch);
                        $countings++;
                    }else{
                        $response = 'ok';
                    }
                    
                    if($response == 'ok_for_all' || $response == 'ok' || $response == 'accept_all'){
                    
                        $uniqueCsvData[$value->$emailField] = $value;
                    }else{
                        $notVerifyCount++;
                    }
                    
                }else{
                    $uniqueCsvData[$value->$emailField] = $value;
                }
            }else{
                $duplicateContactCount +=1; 
            }

            if(count($checkBlacklist)>0 ){
               
                unset($uniqueCsvData[$value->$emailField]);
                $blacklistCount +=1; 
                
            }
            
        }
        $subscription->email_verification = $subscription->email_verification - $countings;
        $subscription->save();
        $uniqueCsvData = array_values($uniqueCsvData);
        //dd($uniqueCsvData);
        $duplicateCount = $allCount - count($uniqueCsvData);
        // foreach($csvToArray[0]->data as $key=> $value){
        //     $values = (array)$value;
        //     $saveData[$emailField] = $values[$emailField];
        //     $saveData[$NameField] = $values[$NameField];
        //     $saveData[$keyName] = $values[$keyName];
        //     //if(array_key_exists())
        //     // if($emailfields == )
        //     // $subscriber = \Acelle\Model\Subscriber::create([
        //     //     'mail_list_id' => $list->id,
        //     //     'email' => $value->$emailField,
        //     //     'status' => 'subscribed',
        //     //     'from' => 'added',

        //     // ]);
        //     // $subscriber->save();
        //     // $subscriberFeild = \Acelle\Model\SubscriberField::create([
        //     //     'subscriber_id' => $subscriber->id,
        //     //     'field_id' => 
        //     // ]);

        // }
        // var_dump($saveData);

        // exit;
        // $from_name = $request->input('from_name');
        // $sending_server = $request->input('sending_server');
        // $sending_server_details = \Acelle\Model\SendingServer::where('uid',$sending_server)->first();
        // $email = $sending_server_details->default_from_email;


        // $customer = $request->user()->customer;
        // $contact = \Acelle\Model\Contact::create([
        //     'email' => $email
        // ]);
        // $contact->save();
        // $list = new \Acelle\Model\MailList();
        // $ada = array([
        //     'name' => Date('Y-m-d'),
        //     'customer_id'=> $customer->id,
        //     'contact_id' => $contact->id, 

        // ]);
        // //exit;
        // $list->fill($ada);
        // $list->customer_id = $customer->id;
        // $list->contact_id = $contact->id;
        // $list->status = 'active';

        // // var_dump($list);
        // // exit;
        // $list->save();
        $list = \Acelle\Model\MailList::findOrFail($request->input('list_id'));

        
        $field = "";
        foreach($uniqueCsvData as $key=> $value){
            if($key == 0 ){
                $subscribers = \Acelle\Model\Subscriber::where('mail_list_id',$list->id)->get();
                if(count($subscribers) == 0){
                    $emailfields = \Acelle\Model\Field::create([
                        'mail_list_id' => $list->id,
                        'type' => 'text',
                        'label' => 'Email',
                        'required' => '1',
                        'tag' => 'EMAIL',

                    ]);
                    $emailfields->save();
                    $namefields = \Acelle\Model\Field::create([
                        'mail_list_id' => $list->id,
                        'type' => 'text',
                        'label' => 'First name',
                        'required' => '0',
                        'tag' => 'FIRST_NAME',

                    ]);
                    $namefields->save();
                    $companyFields = \Acelle\Model\Field::create([
                        'mail_list_id' => $list->id,
                        'type' => 'text',
                        'label' => 'Company',
                        'required' => '0',
                        'tag' => 'COMPANY',

                    ]);
                    $companyFields->save();
                    // if(strtolower($keyName) == 'phone' || strtolower($keyName) == 'mobile no'){
                    //     $phonefields = \Acelle\Model\Field::create([
                    //         'mail_list_id' => $list->id,
                    //         'type' => 'number',
                    //         'label' => 'Phone',
                    //         'required' => '0',
                    //         'tag' => 'PHONE',

                    //     ]);
                    //     $phonefields->save();
                    // }else{
                    //     $phonefields = \Acelle\Model\Field::create([
                    //         'mail_list_id' => $list->id,
                    //         'type' => 'text',
                    //         'label' => $keyName,
                    //         'required' => '0',
                    //         'tag' => strtoupper($keyName),

                    //     ]);
                    //     $phonefields->save();
                    // }
                }else{
                   $emailfields = \Acelle\Model\Field::where('mail_list_id',$list->id)->where('label', 'Email')->first();
                    $namefields = \Acelle\Model\Field::where('mail_list_id',$list->id)->where('label', 'First name')->first();
                    $companyFields = \Acelle\Model\Field::where('mail_list_id',$list->id)->where('label', 'Company')->first();
                    // if(strtolower($keyName) == 'phone' || strtolower($keyName) == 'mobile no'){
                    //     $phonefields = \Acelle\Model\Field::where('mail_list_id',$list->id)->where('label', 'Phone')->first();
                    // }else{
                    //     $phonefields = \Acelle\Model\Field::where('mail_list_id',$list->id)->where('label', strtoupper($keyName))->first();
                    // }
                        
                }

                // if(isset($phonefields) && isset($phonefields->id)){
                //     $phonefields = \Acelle\Model\Field::create([
                //             'mail_list_id' => $list->id,
                //             'type' => 'text',
                //             'label' => $keyName,
                //             'required' => '0',
                //             'tag' => strtoupper($keyName),

                //         ]);
                //         $phonefields->save();
                // }
            }
            $values = (array)$value;
            $saveData[$emailField] = $values[$emailField];
            $saveData[$NameField] = $values[$NameField];
            //$saveData[$keyName] = $values[$keyName];
            
            //if($emailfields == )
            $subscriber = \Acelle\Model\Subscriber::create([
                'mail_list_id' => $list->id,
                'email' => $values[$emailField],
                'status' => 'subscribed',
                'from' => 'added',

            ]);

            
            $subscriber->ip = $request->ip();
            $subscriber->status = 'subscribed';
            $subscriber->save();

            
            $subscriberFeild = \Acelle\Model\SubscriberField::create([
                'subscriber_id' => $subscriber->id,
                'field_id' => $emailfields->id,
                'value' => $values[$emailField],
            ]);
            $subscriberFeild->save();
            $subscriberFeild = \Acelle\Model\SubscriberField::create([
                'subscriber_id' => $subscriber->id,
                'field_id' => $namefields->id,
                'value' => $values[$NameField],
            ]);
            $subscriberFeild->save();
            $subscriberFeild = \Acelle\Model\SubscriberField::create([
                'subscriber_id' => $subscriber->id,
                'field_id' => $companyFields->id,
                'value' => $values[$CompanyField],
            ]);
            $subscriberFeild->save();
            // $subscriberFeild = \Acelle\Model\SubscriberField::create([
            //     'subscriber_id' => $subscriber->id,
            //     'field_id' => $phonefields->id,
            //     'value' => $values[$keyName],
            // ]);
            // $subscriberFeild->save();

            event(new \Acelle\Events\MailListUpdated($subscriber->mailList));
            MailListSubscription::dispatch($subscriber);

        }
        //if($duplicateCount > 0){
            return response()->json(['status'=> true,'DuplicateCount'=> $duplicateContactCount,'BlacklistCount'=>$blacklistCount,'notVerifyCount' => $notVerifyCount]);
        // }
        // return response()->json(['status'=> true]);
    }

    public function importContactManualy(Request $request){
        //dd($request->wholecsvdata);

        $emailField = $request->input('EmailField');
        $NameField = $request->input('NameField');
        $lastName = $request->input('LastName');
        $wholecsvdata = $request->wholecsvdata;
        $Email_Validate = $request->input('Email_Validate');

        $csvToArray = explode(",",$wholecsvdata);
        //var_dump($wholecsvdata);
        $saveData = array();

        $allCount = count($csvToArray);
        $csvData = $csvToArray;
        // foreach($csvToArray[0]->data as $key=> $value){
        //     $values = (array)$value;
        //     $validation = Validator::make($values, [$emailField => 'required|email']);
        //     if ($validation->fails()) {
        //         return response()->json(['status'=>false,'error' => $validation->errors()]);
        //     }
        // }
        $uniqueCsvData = array();
        $invalidEmails = [];
        // $duplicateContactCount = 0;
        // $blacklistCount = 0;
        // foreach($csvData as $key=>$value)
        // {
        //     $dataCheck = \Acelle\Model\Subscriber::where('email',$value->$emailField)->where('mail_list_id',$list->id)->get();
        //     $checkBlacklist = \Acelle\Model\Blacklist::where('email',$value->$emailField)->get();
        //     if(!count($dataCheck)>0 ){
        //         if(!isset($uniqueCsvData[$value->$emailField]))
        //         {
                    
        //             $uniqueCsvData[$value->$emailField] = $value;
                    
        //         }
        //     }else{
        //         $duplicateContactCount +=1; 
        //     }

        //     if(count($checkBlacklist)>0 ){
               
        //         unset($uniqueCsvData[$value->$emailField]);
        //         $blacklistCount +=1; 
                
        //     }
            
        // }

        // $uniqueCsvData = array_values($uniqueCsvData);
        // //dd($uniqueCsvData);
        // $duplicateCount = $allCount - count($uniqueCsvData);
        
        // foreach($csvData as $key=>$value)
        // {
        //     if(!isset($uniqueCsvData[$value->email]))
        //     {
                
        //         $uniqueCsvData[$value->email] = $value;
                
        //     }
        // }

        
        //dd($lastName);
        $list = \Acelle\Model\MailList::findOrFail($request->input('list_id'));
        $subscribers = \Acelle\Model\Subscriber::where('mail_list_id',$list->id)->get();
        //dd(count($subscribers));
        if(count($subscribers) == 0){

            $emailfields = \Acelle\Model\Field::create([
                'mail_list_id' => $list->id,
                'type' => 'text',
                'label' => 'Email',
                'required' => '1',
                'tag' => 'EMAIL',

            ]);
            $emailfields->save();
            $namefields = \Acelle\Model\Field::create([
                'mail_list_id' => $list->id,
                'type' => 'text',
                'label' => 'First name',
                'required' => '0',
                'tag' => 'FIRST_NAME',

            ]);
            $namefields->save();
            
                $phonefields = \Acelle\Model\Field::create([
                    'mail_list_id' => $list->id,
                    'type' => 'text',
                    'label' => str_replace('_',' ',$lastName),
                    'required' => '0',
                    'tag' => strtoupper($lastName),

                ]);
                $phonefields->save();

                $companyField = \Acelle\Model\Field::create([
                    'mail_list_id' => $list->id,
                    'type' => 'text',
                    'label' => 'Company',
                    'required' => '0',
                    'tag' => 'COMPANY',

                ]);
                $companyField->save();
        }else{

           $emailfields = \Acelle\Model\Field::where('mail_list_id',$list->id)->where('label', 'Email')->first();
            
            $namefields = \Acelle\Model\Field::where('mail_list_id',$list->id)->where('label', 'First name')->first();
            
            $phonefields = \Acelle\Model\Field::where('mail_list_id',$list->id)->where('label', $lastName)->first();
            //dd($phonefields);

            $companyField = \Acelle\Model\Field::where('mail_list_id',$list->id)->where('label', "Company")->first();
            if($companyField == null){
                $companyField = \Acelle\Model\Field::create([
                    'mail_list_id' => $list->id,
                    'type' => 'text',
                    'label' => 'Company',
                    'required' => '0',
                    'tag' => 'COMPANY',

                ]);
                $companyField->save();
            }
                
        }

        //dd($$phonefields);
        $field = "";
        $notVerifyCount =0;
        foreach($csvToArray as $key=> $value){
            $companyVal = explode("[",$value);
            $company = str_replace(']','',$companyVal[1]);
            //dd($company);
            $explodeArray = explode('=',$companyVal[0]);

            $name = str_replace('=','',$explodeArray[0]);
            $email = str_replace('=','',$explodeArray[1]);
            $nameExplode = explode(' ',$name);
            $values = (array)$value;
            $dataCheck = \Acelle\Model\Subscriber::where('email',trim($email," "))->where('mail_list_id',$list->id)->get();
            //dd($email);
            if(!count($dataCheck)>0){


            if(!isset($uniqueCsvData[$email]))
            {
                
                $server = EmailVerificationServer::where('status','active')->where('type','emaillistverify.com')->first();
                //dd($server);
                if(!empty($server) && $Email_Validate == 1){
                    $optionData = json_decode($server->options,true);
                    $email = $email;
                    $key = $optionData['api_key'];
                    $url = "https://apps.emaillistverify.com/api/verifyEmail?secret=".$key."&email=".$email;
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true );
                    $response = curl_exec($ch);
                    //echo $response;
                    curl_close($ch);
                }else{
                    $response = 'ok';
                }
                
                if($response == 'ok_for_all' || $response == 'ok' || $response == 'accept_all'){
                    $uniqueCsvData[$email] = $value;
                
                    $saveData[$emailField] = $email;
                    $saveData[$NameField] = $nameExplode[0];
                    $saveData[$lastName] = $nameExplode[1];
                    
                    //if($emailfields == )
                    $subscriber = \Acelle\Model\Subscriber::create([
                        'mail_list_id' => $list->id,
                        'email' => trim($email," "),
                        'status' => 'subscribed',
                        'from' => 'added',

                    ]);

                    
                    $subscriber->ip = $request->ip();
                    $subscriber->status = 'subscribed';
                    $subscriber->save();

                    
                    $subscriberFeild = \Acelle\Model\SubscriberField::create([
                        'subscriber_id' => $subscriber->id,
                        'field_id' => $emailfields->id,
                        'value' => trim($email," "),
                    ]);
                    $subscriberFeild->save();
                    $subscriberFeild = \Acelle\Model\SubscriberField::create([
                        'subscriber_id' => $subscriber->id,
                        'field_id' => $namefields->id,
                        'value' => str_replace('"','',$nameExplode[0]),
                    ]);
                    $subscriberFeild->save();
                    if($phonefields != NULL){
                        $subscriberFeild = \Acelle\Model\SubscriberField::create([
                            'subscriber_id' => $subscriber->id,
                            'field_id' => $phonefields->id,
                            'value' => str_replace('"','',$nameExplode[1]),
                        ]);
                        $subscriberFeild->save();
                    }
                    

                    $subscriberFeild = \Acelle\Model\SubscriberField::create([
                        'subscriber_id' => $subscriber->id,
                        'field_id' => $companyField->id,
                        'value' => str_replace('"','',$company),
                    ]);
                    $subscriberFeild->save();

                    event(new \Acelle\Events\MailListUpdated($subscriber->mailList));
                    MailListSubscription::dispatch($subscriber);
                    
                }else{
                    $notVerifyCount++;
                }
                
            }
            }
            

        }
        $uniqueCsvData = array_values($uniqueCsvData);
        $duplicateCount = $allCount - count($uniqueCsvData);

        return response()->json(['status'=> true,'count'=> $duplicateCount,'notVerifyCount'=>$notVerifyCount]);
        //$customer = $request->user()->customer;
    }

    public function createSequenceTemplate(Request $request)
    {
       
        $customer = $request->user()->customer;
        
        $type = $request->type;
        $sequenceId = $request->id;
        $template = Template::where('customer_id',$customer->id)->get();
        $uid = $request->uid;
        $clickType = isset($request->clickType) ? $request->clickType : '';
        $elementId = isset($request->elementId) ? $request->elementId : '';
        $automation = Automation2::findByUid($uid);
        if($automation != null){
            $tags = Field::where('mail_list_id',$automation->mail_list_id)->get();
        }else{
            $automation = AutomationList::findByUid($uid);
            $tags = Field::where('mail_list_id',$automation->mail_list_id)->get();
        }
        
        return view('automation::email.create',compact('type','sequenceId','template','uid','tags','clickType','elementId'));
    }

    public function storeTemplate(Request $request)
    {
        // authorize
        if (!$request->user()->customer->can('create', Template::class)) {
            return $this->notAuthorized();
        }

        $validated = Validator::make($request->all(),[
            'subject' => 'required',
            'content' => 'required',
            'name' => 'required',
        ]);

        if($validated->fails()){
            return response()->json(['status' => false,'msg'=>'Please Fill Details FIrst']);
        }

        $template = Template::saveTemplate($request); 

        $templateData = Template::findByUid($template->uid);
        $type = $request->type;
        $sequenceId = $request->sequenceId;
        $clickType = $request->clickType;
        $elementId = $request->elementId;

    
        return response()->json(['status'=> true, 'data' => $templateData, 'type'=> $type,'sequenceId'=> $sequenceId,'uid'=>$request->uid,'clickType'=>$clickType,'elementId'=>$elementId]);
    }

    public function getTempalte(Request $request){

        $templateData = Template::findOrFail($request->Templates);
        $updateTemplate = Template::updateTemplate($templateData->uid,$request);
        $type = $request->type;
        $sequenceId = $request->sequenceId;
        $clickType = $request->clickType;
        $elementId = $request->elementId;
    
        return response()->json(['status'=> true, 'data' => $updateTemplate, 'type'=> $type,'sequenceId'=> $sequenceId,'uid'=>$request->uid,'clickType'=>$clickType,'elementId'=> $elementId]);
    }

    public function saveResponse(Request $request){

        // var_dump($request);
        // exit;
        $token = \LaravelGmail::makeToken();
        $checkAlready = \Acelle\Model\SendingServer::where('default_from_email',$token['email'])->where('customer_id',$request->user()->customer->id)->get();
        if(count($checkAlready)>0){

        }else{
            //dd(\LaravelGmail::user());
            $sending_server = new \Acelle\Model\SendingServer();
            $sending_server->customer_id= $request->user()->customer->id;
            $sending_server->name = \LaravelGmail::user();
            $sending_server->type = 'Gmail';
            $sending_server->default_from_email = $token['email'];
            $sending_server->quota_value = 1000;
            $sending_server->quota_base = 1;
            $sending_server->quota_unit = 'hour';
            $sending_server->status = 'active';
            $sending_server->aws_access_key_id = $token['access_token'];
            $sending_server->aws_secret_access_key = $token['refresh_token'];
            $sending_server->token_mail = json_encode($token);
            $sending_server->save();
            $sender = new \Acelle\Model\Sender();
            $sender->customer_id= $request->user()->customer->id;
            $sender->name = 'Gmail '.$token['email'];
            $sender->email = $token['email'];
            $sender->status = 'verified';
            $sender->sending_server_id = $sending_server->id;
            
            unlink(storage_path('app/gmail/tokens/gmail-json-'.Auth::user()->id.'.json'));
            // $messages = \LaravelGmail::message();
            // var_dump($message);
            // exit;
            // foreach ( $messages as $message ) {
            //     $body = $message->getHtmlBody();
            //     $subject = $message->getSubject();
            //     var_dump($body);
            //     var_dump($subject);
            //     exit;
            // }
        }
        
        return redirect('/');
        
    }

    public function tempalateSelect($id){
        $template = Template::findOrFail($id);
        return json_encode($template);
    }

    public function updateData(Request $request){
        // echo "<pre>";
        // print_r($request->all());
        // exit;
        $automation = Automation2::findByUid($request->input('automation_uid'));
        $automationData = json_encode($automation->data);
        $data[] = $automationData[0];
        $mail_list = \Acelle\Model\MailList::where('id',$automation->mail_list_id)->first();
             $subscriberData = \Acelle\Model\Subscriber::where('mail_list_id',$mail_list->id)->get();
             $listCount = count($subscriberData)/$request->input('segmentNumber');
             if($listCount>0){
                $finalListCount = round($listCount); 
             }else{
                $finalListCount = 0; 
             }
             $listNumber = 1;
             if($finalListCount == 0 ){
               foreach($subscriberData as $value){
                    $subscriberDatas[$listNumber][] = $value->email;
                    
                } 
             }else{
                foreach($subscriberData as $key=> $value){
                    if($key+1 <= $finalListCount){
                        $subscriberDatas[$listNumber][] = $value->email;
                        
                    }else{
                        $listNumber++;
                        $finalListCount += $finalListCount;
                        $subscriberDatas[$listNumber][] = $value->email;
                    }
                }
             }
             // echo "<pre>";
             // var_dump($subscriberDatas);
             // exit;
            $sending_server_details = \Acelle\Model\SendingServer::where('id',$automation->smtp_server_id)->first();
        for($i = 1; $i<=$request->input('segmentNumber'); $i++){

            for ($j=0; $j <count($request->child[$i]) ; $j++) {
            
            $template = Template::findByUid($request->template_uid[$i][$j]);
           
            if($template == null){
                $email = Email::findByUid($request->template_uid[$i][$j]);
                
            }else{
                $email = new Email();
                $email->automation2_id = $automation->id;
                $email->subject = $template->subject;
                $email->from = $sending_server_details->default_from_email;
                $email->from_name = 'test';
                $email->reply_to = $sending_server_details->default_from_email;
                $email->sign_dkim = 1;
                $email->track_open = 1;
                $email->track_click = 1;
                $email->action_id = $request->id[$i][$j];
                $email->plain = $template->content;
                
                $email->template_id = $template->id;
                $email->customer_id = Auth::user()->customer->id;
                $email->save();
            }
            

                $optionsArray = array(
                    "init"=> "true",
                      "email_uid"=> $email->uid,
                      "template"=> "true",
                      'subscribers' => (count($subscriberDatas) <= $i) ? $subscriberDatas[$i]: $subscriberDatas[$i],
                );
                if($j == count($request->child[$i])-1){
                    if($i == $request->input('segmentNumber')){
                        $child = null;
                    
                    }else{
                        $k = $i+1;
                        $child = (int)$request->id[$k][0];
                    }
                }else{
                    $child = (int)$request->id[$i][$j+1];
                }

                $data[] = array(
                    "id"=>(int)$request->id[$i][$j],
                    "title"=> $template->subject,
                    "type"=> 'ElementAction',
                    'segmentNumber' => $i,
                    "child"=> $child,
                    "options"=> $optionsArray,
                    "last_executed"=> null,
                    "evaluationResult"=> null,
                    
                );
            }

            

        }
        
         $automation->data = json_encode($data);
         $automation->save();
         return redirect('automation2/'.$automation->uid.'/edit/');
    }
    // public function store(Request $request)
    // {
        
    //     // echo "<pre>";
    //     // var_dump($request->all());
    //     // exit;
    //     $optionsArray = array(
    //             "init"=> true,
    //               "type"=> "datetime",
    //               "date"=> toDateString(\Carbon\Carbon::now()),
    //               "at" => date('h:i A'),
    //               "key" => 'specific-date',
    //         );

    //     $data[] = array(
    //             "id"=>"trigger",
    //             "title"=> "Autostart on a scheduled date/time",
    //             "type"=> 'ElementTrigger',
    //             "child"=> (int)$request->id[1][0],
    //             "options"=> $optionsArray,
    //             "last_executed"=> null,
    //             "evaluationResult"=> null,
                
    //         );
    //     $automation = Automation2::findByUid($request->input('automation_uid'));
    //         $mail_list = \Acelle\Model\MailList::where('id',$automation->mail_list_id)->first();
    //          $subscriberData = \Acelle\Model\Subscriber::where('mail_list_id',$mail_list->id)->get();
    //          $listCount = count($subscriberData)/$request->input('segmentNumber');
    //          if($listCount>0){
    //             $finalListCount = round($listCount); 
    //          }else{
    //             $finalListCount = 0; 
    //          }
    //          $listNumber = 1;
    //          if($finalListCount == 0 ){
    //            foreach($subscriberData as $value){
    //                 $subscriberDatas[$listNumber][] = $value->email;
                    
    //             } 
    //          }else{
    //             foreach($subscriberData as $key=> $value){
    //                 if($key+1 <= $finalListCount){
    //                     $subscriberDatas[$listNumber][] = $value->email;
                        
    //                 }else{
    //                     $listNumber++;
    //                     $finalListCount += $finalListCount;
    //                     $subscriberDatas[$listNumber][] = $value->email;
    //                 }
    //             }
    //          }
    //          // echo "<pre>";
    //          // var_dump($subscriberDatas);
    //          // exit;
    //         $sending_server_details = \Acelle\Model\SendingServer::where('id',$automation->smtp_server_id)->first();
    //     for($i = 1; $i<=$request->input('segmentNumber'); $i++){

    //         for ($j=0; $j <count($request->child[$i]) ; $j++) {
            
    //         $template = Template::findByUid($request->template_uid[$i][$j]);
           
            
    //         $email = new Email();
    //         $email->automation2_id = $automation->id;
    //         $email->subject = $template->subject;
    //         $email->from = $sending_server_details->default_from_email;
    //         $email->from_name = 'test';
    //         $email->reply_to = $sending_server_details->default_from_email;
    //         $email->sign_dkim = 1;
    //         $email->track_open = 1;
    //         $email->track_click = 1;
    //         $email->action_id = $request->id[$i][$j];
    //         $email->plain = $template->content;
            
    //         $email->template_id = $template->id;
    //         $email->customer_id = Auth::user()->customer->id;
    //         $email->save();

    //             $optionsArray = array(
    //                 "init"=> "true",
    //                   "email_uid"=> $email->uid,
    //                   "template"=> "true",
    //                   'subscribers' => (count($subscriberDatas) <= $i) ? $subscriberDatas[$i]: '',
    //             );
    //             if($j == count($request->child[$i])-1){
    //                 if($i == $request->input('segmentNumber')){
    //                     $child = null;
                    
    //                 }else{
    //                     $k = $i+1;
    //                     $child = (int)$request->id[$k][0];
    //                 }
    //             }else{
    //                 $child = (int)$request->id[$i][$j+1];
    //             }

    //             $data[] = array(
    //                 "id"=>(int)$request->id[$i][$j],
    //                 "title"=> "Send email `Welcom",
    //                 "type"=> 'ElementAction',
    //                 'segmentNumber' => $i,
    //                 "child"=> $child,
    //                 "options"=> $optionsArray,
    //                 "last_executed"=> null,
    //                 "evaluationResult"=> null,
                    
    //             );
    //         }

            

    //     }
    //     // //echo "<pre>";
    //     // print_r(json_encode($data));
    //     // exit;
    //     $automation = Automation2::findByUid($request->input('automation_uid'));
    //     $automation->data = json_encode($data);
    //     $automation->save();
    //     return redirect('automation/step4/'.$automation->uid);

    //     var_dump(json_encode($data));
    // }
    public function checkSubscriber($uid){
        $automation = AutomationList::findByUid($uid);
        $subscribers = $automation->subscribers();
        $count = $subscribers->count();
        if($count>0){
            return true;
        }
        return false;
    }

    public function checkSubscriberSgment2($uid){
        $automation = AutomationList::findByUid($uid);
        $subscribers = $automation->subscribers();
        $count = $subscribers->count();
        if($count>1){
            return true;
        }
        return false;
    }

    public function checkSendServer($uid){
        
        $automations = AutomationList::findByUid($uid);
        if($automations->smtp_server_id != '' ){
            return true;
        }
        return false;
    }

    public function showAddTime($uid){
        $automation = Automation2::findByUid($uid);
        $trigger = $automation->getTrigger();
        $rules = $this->triggerRules()['specific-date'];
        return view('automation::startTime',compact('automation','trigger','rules'));
    }

    public function updateTime(Request $request){
        $automation = Automation2::findByUid($request->uid);
        $data = json_decode($automation->data);
        $data[0]->options->date = $request->options['date'];
        $data[0]->options->at = $request->options['at'];
        $automation->data = json_encode($data);
        $automation->save();
        return redirect()->back();

    }

    public function EditTemplate(Request $request,$template_id,$email_id){
        $template = Template::findOrFail($template_id);
        $customer = $request->user()->customer;
        $templates = Template::where('customer_id',$customer->id)->get();
        return view('automation::email.edit',compact('template','email_id','templates'));
    }

    public function updateTemplate(Request $request)
    {
        // authorize
        if (!$request->user()->customer->can('create', Template::class)) {
            return $this->notAuthorized();
        }

        $validated = Validator::make($request->all(),[
            'subject' => 'required',
            'content' => 'required',
            'name' => 'required',
        ]);

        if($validated->fails()){
            return response()->json(['status' => false,'msg'=>'Please Fill Details FIrst']);
        }
        $templateUid = Template::findOrFail($request->template_id);
        $newRequest = array(
            'subject' => $request->subject,
            'content' => $request->content,
            'name' => $request->name,
        );
        $template = Template::updateTemplate($templateUid->uid,(object)$newRequest); 

        $templateData = Template::findByUid($template->uid);

        $email = Email::findOrFail($request->email_id);
            $email->subject = $template->subject;
            $email->plain = $template->content;
            $email->template_id = $template->id;
            $email->save();
        
        return response()->json(['status'=> true, 'data' => $templateData, 'uid'=>$templateUid->uid]);
    }


    public function getDay($key,$uid){
        $automation = Automation2::findByUid($uid);
        $data = json_decode($automation->data,true);

        $days = $data[$key]['options']['time'];
        $daysExclude = explode(' ',$days);
        $day = $daysExclude[0];
        $unit = $daysExclude[1];
        return view('automation::changeDay',compact('automation','day','unit','key'));
    }

    public function getDay1($key,$uid){
        $automation = Automation2::findByUid($uid);
        $data = json_decode($automation->data,true);

        $days = $data[$key]['options']['wait'];
        $daysExclude = explode(' ',$days);
        $day = $daysExclude[0];
        $unit = $daysExclude[1];
        return view('automation::changeDay1',compact('automation','day','unit','key'));
    }

    public function updateWait(Request $request){
        $automation = Automation2::findByUid($request->uid);
        $data = json_decode($automation->data,true);
        $newData = array();
        foreach($data as $keys => $datas){
            if($keys == $request->key){
                $datas['options']['time'] = $request->day.' '.$request->unit;
                $datas['title'] = 'Wait for '.$request->day.' '.$request->unit;
            }
            $newData[]=$datas;
        }
        $automation->data = json_encode($newData);
        $automation->save();

        return response()->json(['status' => true]);
    }

    public function updateWait1(Request $request){
        $automation = Automation2::findByUid($request->uid);
        $data = json_decode($automation->data,true);
        $newData = array();
        foreach($data as $keys => $datas){
            if($keys == $request->key){
                $datas['options']['wait'] = $request->day.' '.$request->unit;
            }
            $newData[]=$datas;
        }
        $automation->data = json_encode($newData);
        $automation->save();

        return response()->json(['status' => true]);
    }

    function deleteSegment($uid){
        $automation = Automation2::findByUid($uid);
        $data = json_decode($automation->data,true);
        $id = $data[1]['id'];
        $automationTrigger =  AutoTrigger::whereRaw('FIND_IN_SET(?,executed_index)',[$id])->get();
        if(count($automationTrigger)>0){
            return response()->json(['status' => false]);
        }
        //$automationList = Automation2::where('main_id',$automation->main_id)
        $automation->delete();
        return response()->json(['status' => true]);
    }

    public function deleteSequence($uid,$key)
    {
        $automation = Automation2::findByUid($uid);
        $data = json_decode($automation->data,true);
        $newData = array();
        $id = $data[$key-1]['id'];
        $automationTrigger =  AutoTrigger::whereRaw('FIND_IN_SET(?,executed_index)',[$id])->get();
        if(count($automationTrigger)>0){
            return response()->json(['status' => false]);
        }
        foreach($data as $keys => $value){
            if($keys < $key-1){
                $newData[] = $value;
                if($keys == $key-2){
                    $newData[$key-2]['child'] = null;
                }
            }
        }
        
        $automation->data = json_encode($newData,true);

        $automation->save();
        return response()->json(['status' => true]);

    }

    public function quickView(Request $request)
    {
      if($request->uid !=""){
        $AutomationList = AutomationList::findByUid($request->uid);

        $automation = Automation2::where('main_id',$AutomationList->id)->get();
        //dd($automation);

        return view('automation::_quick_view', [
            'main_id' => $AutomationList->id,
            'AutomationList' => $AutomationList,
        ]); 
      }
    }

    /**
     * Chart.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function chart($main_id)
    {
        $automationsB = array();

          $totalDeliver = 0;
        $totalFailed = 0;
        $totalOpen = 0;
        $totalOpenCount = [];
        $totalOpenCountA = [];
        $totalOpenCountB = [];
        $totalOpenDates = [];
        $totalOpenDatesA = [];
        $totalOpenDatesB = [];
        $totalDelivers = [];
        $totalDeliversCount = [];
        $totalDeliversCountA = [];
        $totalDeliversCountB = [];
        $totalDeliversDates = [];
        $totalDeliversDatesA = [];
        $totalDeliversDatesB = [];
        $totalOpenCountB = [];
        $totalOpenCountA = [];
        $finalOpenCountA = array();
        $finalOpenCountB = array();
        $finalDeliverCountA = array();
        $finalDeliverCountB = array();
        $newDeliverDateArray = array();
         $newOperDateArray = array();
         $totalOpensAUnique = 0;
         $total = 0;
         $uniquepercentB = 0;
         $uniquepercentA = 0;

       	$totalClick = 0;
        $totalClickCount = [];
        $totalClickCountA = [];
        $totalClickCountB = [];
        $totalClickDates = [];
        $totalClickDatesA = [];
        $totalClickDatesB = [];
        $totalClickCountB = [];
        $totalClickCountA = [];
        $finalClickCountA = array();
        $finalClickCountB = array();
        $newClickDateArray = array();

        $totalBounce = 0;
        $totalBounceCount = [];
        $totalBounceCountA = [];
        $totalBounceCountB = [];
        $totalBounceDates = [];
        $totalBounceDatesA = [];
        $totalBounceDatesB = [];
        $totalBounceCountB = [];
        $totalBounceCountA = [];
        $finalBounceCountA = array();
        $finalBounceCountB = array();
        $newBounceDateArray = array();

        $automation = Automation2::where('main_id',$main_id)->get();
        $AutomationList = AutomationList::findOrFail($main_id);
        
        //dd($automation);
        if($automation->count()>0){
          $total = json_decode($automation[0]['cache'],true)['SummaryStats']['total'];
         // dd($total);
            $automationsA = Automation2::findByUid($automation[0]->uid);

              $totalOpensA = $automationsA->openLogs_new();
              $totalOpensAUnique = $automationsA->openLogs();
              if($total>0){
                $uniquepercentA = round((($totalOpensAUnique/ $total)*100),2);
              }else{
                $uniquepercentA = 0;
              }


              
           // dd($automationsA->openLogs());
            if(!empty($totalOpensA) && $totalOpensA->count()>0){
                if($totalOpensA->count() == 1){

                    foreach($totalOpensA as $totalOpenssA){
                    $totalOpenCountA[] = 0;    
                    $totalOpenDatesA[] = date('Y-m-d', strtotime('-1 day', strtotime($totalOpenssA['createDate'])));    
                    $totalOpenCountA[] = $totalOpenssA['count(open_logs.id)'];
                    $totalOpenDatesA[] = $totalOpenssA['createDate'];
                }
            }else{

                foreach($totalOpensA as $key => $totalOpenssA){
                    if($key == 0){
                        $totalOpenCountA[] = 0;    
                        $totalOpenDatesA[] = date('Y-m-d', strtotime('-1 day', strtotime($totalOpenssA['createDate'])));
                    }    
                    $totalOpenCountA[] = $totalOpenssA['count(open_logs.id)'];
                    $totalOpenDatesA[] = $totalOpenssA['createDate'];
                }
            }
                

            }

            $totalDeliversA = $automationsA->deliveredCount_New();
            $totalDeliversAUnique = $automationsA->deliveredCount();
            //dd($totalDeliversAUnique);
              if(!empty($totalDeliversA) && $totalDeliversA->count()>0){
                if($totalDeliversA->count() == 1){
                       foreach($totalDeliversA as $totalDeliverssA){
                    $totalDeliversCountA[] = 0;    
                    $totalDeliversDatesA[] = date('Y-m-d', strtotime('-1 day', strtotime($totalDeliverssA['createDate'])));    
                    $totalDeliversCountA[] = $totalDeliverssA['countData'];
                    $totalDeliversDatesA[] = $totalDeliverssA['createDate'];
                }
            }else{

                 foreach($totalDeliversA as $key => $totalDeliverssA){
                    if($key == 0){
                        $totalDeliversCountA[] = 0;    
                        $totalDeliversDatesA[] = date('Y-m-d', strtotime('-1 day', strtotime($totalDeliverssA['createDate'])));
                    }
                    $totalDeliversCountA[] = $totalDeliverssA['countData'];
                    $totalDeliversDatesA[] = $totalDeliverssA['createDate'];
                }
            }

            }

            $totalClickA = $automationsA->clickLogs_new();
            $totalClickAUnique = $automationsA->clickLogs();
                        
              
           // dd($automationsA->openLogs());
            if(!empty($totalClickA) && $totalClickA->count()>0){
                if($totalClickA->count() == 1){

                    foreach($totalClickA as $totalClicksA){
                    $totalClickCountA[] = 0;    
                    $totalClickDatesA[] = date('Y-m-d', strtotime('-1 day', strtotime($totalClicksA['createDate'])));    
                    $totalClickCountA[] = $totalClicksA['count(click_logs.id)'];
                    $totalClickDatesA[] = $totalClicksA['createDate'];
                }
            }else{

                foreach($totalClickA as $key => $totalClicksA){
                    if($key == 0){
                        $totalClickCountA[] = 0;    
                        $totalClickDatesA[] = date('Y-m-d', strtotime('-1 day', strtotime($totalClicksA['createDate'])));
                    }
                    $totalClickCountA[] = $totalClicksA['count(click_logs.id)'];
                    $totalClickDatesA[] = $totalClicksA['createDate'];
                }
            }
                

            }


            $totalBounceA = $automationsA->bounceLogs_new();
            $totalBounceAUnique = $automationsA->bounceLogs();
                        
              
            //dd($totalBounceA);
            if(!empty($totalBounceA) && $totalBounceA->count()>0){
                if($totalBounceA->count() == 1){

                    foreach($totalBounceA as $totalBouncesA){
                    $totalBounceCountA[] = 0;    
                    $totalBounceDatesA[] = date('Y-m-d', strtotime('-1 day', strtotime($totalBouncesA['createDate'])));    
                    $totalBounceCountA[] = $totalBouncesA['count(bounce_logs.id)'];
                    $totalBounceDatesA[] = $totalBouncesA['createDate'];
                }
            }else{

                foreach($totalBounceA as $key => $totalBouncesA){
                    if($key == 0){
                        $totalBounceCountA[] = 0;    
                        $totalBounceDatesA[] = date('Y-m-d', strtotime('-1 day', strtotime($totalBouncesA['createDate'])));
                    }
                    $totalBounceCountA[] = $totalBouncesA['count(bounce_logs.id)'];
                    $totalBounceDatesA[] = $totalBouncesA['createDate'];
                }
            }
                

            }
        }
         if($automation->count()>1){
            $automationsB = Automation2::findByUid($automation[1]->uid);
             $total = json_decode($automation[1]['cache'],true)['SummaryStats']['total'];
             

            $totalOpensB = $automationsB->openLogs_new();
            $totalOpensBUnique = $automationsA->openLogs();
            if($total>0){
                $total = round($total/2);
                $uniquepercentB = round((($totalOpensBUnique/ $total)*100),2);
              }else{
                $uniquepercentB = 0;
              }
            if(!empty($totalOpensB) && $totalOpensB->count()>0){
                if($totalOpensB->count()==1){
                    foreach($totalOpensB as $totalOpenssB){
                        //for($i)
                        $totalOpenCountB[] = 0;    
                        $totalOpenDatesB[] = date('Y-m-d', strtotime('-1 day', strtotime($totalOpenssB['createDate'])));      
                        $totalOpenCountB[] = $totalOpenssB['count(open_logs.id)'];
                        $totalOpenDatesB[] = $totalOpenssB['createDate'];
                    }
            }else{
                 foreach($totalOpensB as $key => $totalOpenssB){
                    if($key == 0){
                        $totalOpenCountB[] = 0;    
                            $totalOpenDatesB[] = date('Y-m-d', strtotime('-1 day', strtotime($totalOpenssB['createDate'])));
                    }
                    $totalOpenCountB[] = $totalOpenssB['count(open_logs.id)'];
                    $totalOpenDatesB[] = $totalOpenssB['createDate'];
                }
              }
            }

               $totalDeliversB = $automationsB->deliveredCount_New();
               $totalDeliversBUnique = $automationsA->deliveredCount();
              if(!empty($totalDeliversB) && $totalDeliversB->count()>0){
                if($totalOpensB->count()==1){
                   foreach($totalDeliversB as $totalDeliverssB){


                     $totalDeliversCountB[] = 0;    
                    $totalDeliversDatesB[] = date('Y-m-d', strtotime('-1 day', strtotime($totalDeliverssB['createDate'])));  
                    $totalDeliversCountB[] = $totalDeliverssB['countData'];
                    $totalDeliversDatesB[] = $totalDeliverssB['createDate'];
                }
            }else{

                 foreach($totalDeliversB as $key => $totalDeliverssB){
                    if($key == 0){
                        $totalDeliversCountB[] = 0;    
                        $totalDeliversDatesB[] = date('Y-m-d', strtotime('-1 day', strtotime($totalDeliverssB['createDate'])));
                    }
                    $totalDeliversCountB[] = $totalDeliverssB['countData'];
                    $totalDeliversDatesB[] = $totalDeliverssB['createDate'];
                }
              }
            }

            $totalClickB = $automationsB->clickLogs_new();
            $totalClickBUnique = $automationsA->clickLogs();
            
            if(!empty($totalClickB) && $totalClickB->count()>0){
                if($totalClickB->count()==1){
                    foreach($totalClickB as $totalClicksB){
                        //for($i)
                        $totalClickCountB[] = 0;    
                        $totalClickDatesB[] = date('Y-m-d', strtotime('-1 day', strtotime($totalClicksB['createDate'])));      
                        $totalClickCountB[] = $totalClicksB['count(click_logs.id)'];
                        $totalClickDatesB[] = $totalClicksB['createDate'];
                    }
            }else{
                 foreach($totalClickB as $key => $totalClicksB){
                    if($key == 0){
                        $totalClickCountB[] = 0;    
                            $totalClickDatesB[] = date('Y-m-d', strtotime('-1 day', strtotime($totalClicksB['createDate'])));
                    }
                    $totalClickCountB[] = $totalClicksB['count(click_logs.id)'];
                    $totalClickDatesB[] = $totalClicksB['createDate'];
                }
              }
            }



            $totalBounceB = $automationsB->bounceLogs_new();
            $totalBounceBUnique = $automationsA->bounceLogs();
            
            if(!empty($totalBounceB) && $totalBounceB->count()>0){
                if($totalBounceB->count()==1){
                    foreach($totalBounceB as $totalBouncesB){
                        //for($i)
                        $totalBounceCountB[] = 0;    
                        $totalBounceDatesB[] = date('Y-m-d', strtotime('-1 day', strtotime($totalBouncesB['createDate'])));      
                        $totalBounceCountB[] = $totalBouncesB['count(bounce_logs.id)'];
                        $totalBounceDatesB[] = $totalBouncesB['createDate'];
                    }
            }else{
                 foreach($totalBounceB as $key => $totalBouncesB){
                    if($key == 0){
                        $totalBounceCountB[] = 0;    
                        $totalBounceDatesB[] = date('Y-m-d', strtotime('-1 day', strtotime($totalBouncesB['createDate'])));
                    }
                    $totalBounceCountB[] = $totalBouncesB['count(bounce_logs.id)'];
                    $totalBounceDatesB[] = $totalBouncesB['createDate'];
                }
              }
            }
            

           // dd($newOperDateArray,$finalOpenCountA,$finalOpenCountB,$finalDeliverCountA,$finalDeliverCountB);


        }
        if($automation->count()>1){

            $OpenDates = array_merge($totalOpenDatesA,$totalOpenDatesB);
            
            if(count($OpenDates)>0){
            foreach($OpenDates as $dateValue){
                if(!in_array($dateValue, $newOperDateArray)){
                    $newOperDateArray[] = $dateValue;
                }
                
            }
            
            foreach($newOperDateArray as $checkDate){
                if(in_array($checkDate,$totalOpenDatesA)){
                    $finalOpenCountA[] = $totalOpenCountA[array_search($checkDate, $totalOpenDatesA)];
                }else{
                    $finalOpenCountA[] = 0;
                }

                if(in_array($checkDate,$totalOpenDatesB)){
                    $finalOpenCountB[] = $totalOpenCountB[array_search($checkDate, $totalOpenDatesB)]; 
                }else{
                    $finalOpenCountB[] = 0;
                }

            }
         }

            $DeliverDates = array_merge($totalDeliversDatesA,$totalDeliversDatesB);
            //dd(count($DeliverDates),$totalDeliversDatesA,$totalDeliversDatesB);
            if(count($DeliverDates)>0) {
            foreach($OpenDates as $dateValue){
                if(!in_array($dateValue, $newDeliverDateArray)){
                    $newDeliverDateArray[] = $dateValue;
                }
                
            }
       
            foreach($newOperDateArray as $checkDate){
                if(in_array($checkDate,$totalDeliversDatesA)){
                    $finalDeliverCountA[] = $totalDeliversCountA[array_search($checkDate, $totalDeliversDatesA)];
                }else{
                    $finalDeliverCountA[] = 0;
                }

                if(in_array($checkDate,$totalDeliversDatesB)){
                    $finalDeliverCountB[] = $totalDeliversCountB[array_search($checkDate, $totalDeliversDatesB)]; 
                }else{
                    $finalDeliverCountB[] = 0;
                }

            }

        }


        $ClickDates = array_merge($totalClickDatesA,$totalClickDatesB);
            
            if(count($ClickDates)>0){
            foreach($ClickDates as $dateValue){
                if(!in_array($dateValue, $newClickDateArray)){
                    $newClickDateArray[] = $dateValue;
                }
                
            }
            
            foreach($newClickDateArray as $checkDate){
                if(in_array($checkDate,$totalClickDatesA)){
                    $finalClickCountA[] = $totalClickCountA[array_search($checkDate, $totalClickDatesA)];
                }else{
                    $finalClickCountA[] = 0;
                }

                if(in_array($checkDate,$totalClickDatesB)){
                    $finalClickCountB[] = $totalClickCountB[array_search($checkDate, $totalClickDatesB)]; 
                }else{
                    $finalClickCountB[] = 0;
                }

            }
         }


         $BounceDates = array_merge($totalBounceDatesA,$totalBounceDatesB);
            
            if(count($BounceDates)>0){
            foreach($BounceDates as $dateValue){
                if(!in_array($dateValue, $newBounceDateArray)){
                    $newBounceDateArray[] = $dateValue;
                }
                
            }
            
            foreach($newBounceDateArray as $checkDate){
                if(in_array($checkDate,$totalBounceDatesA)){
                    $finalBounceCountA[] = $totalBounceCountA[array_search($checkDate, $totalBounceDatesA)];
                }else{
                    $finalBounceCountA[] = 0;
                }

                if(in_array($checkDate,$totalBounceDatesB)){
                    $finalBounceCountB[] = $totalBounceCountB[array_search($checkDate, $totalBounceDatesB)]; 
                }else{
                    $finalBounceCountB[] = 0;
                }

            }
         }
        }else{
          
            $OpenDates = $totalOpenDatesA;
          // dd($OpenDates);
            foreach($OpenDates as $dateValue){
                if(!in_array($dateValue, $newOperDateArray)){
                    $newOperDateArray[] = $dateValue;
                }
                
            }
            
            foreach($newOperDateArray as $checkDate){
                if(in_array($checkDate,$totalOpenDatesA)){
                    $finalOpenCountA[] = $totalOpenCountA[array_search($checkDate, $totalOpenDatesA)];
                }else{
                    $finalOpenCountA[] = 0;
                }
                

            }


            $DeliverDates = $totalDeliversDatesA;
            
            foreach($OpenDates as $dateValue){
                if(!in_array($dateValue, $newDeliverDateArray)){
                    $newDeliverDateArray[] = $dateValue;
                }
                
            }
       
            foreach($newDeliverDateArray as $checkDate){
                if(in_array($checkDate,$totalDeliversDatesA)){
                    $finalDeliverCountA[] = $totalDeliversCountA[array_search($checkDate, $totalDeliversDatesA)];
                }else{
                    $finalDeliverCountA[] = 0;
                }

            }

            $ClickDates = $totalClickDatesA;
          // dd($ClickDates);
            foreach($ClickDates as $dateValue){
                if(!in_array($dateValue, $newClickDateArray)){
                    $newClickDateArray[] = $dateValue;
                }
                
            }
            
            foreach($newClickDateArray as $checkDate){
                if(in_array($checkDate,$totalClickDatesA)){
                    $finalClickCountA[] = $totalClickCountA[array_search($checkDate, $totalClickDatesA)];
                }else{
                    $finalClickCountA[] = 0;
                }
                

            }


            $BounceDates = $totalBounceDatesA;
          // dd($BounceDates);
            foreach($BounceDates as $dateValue){
                if(!in_array($dateValue, $newBounceDateArray)){
                    $newBounceDateArray[] = $dateValue;
                }
                
            }
            
            foreach($newBounceDateArray as $checkDate){
                if(in_array($checkDate,$totalBounceDatesA)){
                    $finalBounceCountA[] = $totalBounceCountA[array_search($checkDate, $totalBounceDatesA)];
                }else{
                    $finalBounceCountA[] = 0;
                }
                

            }
        }
        $totalOpensDataA = 0;
        $totalOpensUniqueDataA = 0;
        $totalOpensDataB = 0;
        $totalOpensUniqueDataB = 0;
        $totalOpenUniquePercentageA =0;
        $totalOpenUniquePercentageB = 0;
        foreach($automation as $key => $automations){
            if($key == 0){
                $totalOpensDataA = $automations->openLogs();
                $totalOpensUniqueDataA = $automations->openLogsUnique();
                if($totalOpensDataA > 0){
                    // $totalOpenUniquePercentageA = $this->cal_percentage($totalOpensUniqueDataA,$totalOpensDataA);
                    $totalOpenUniquePercentageA = $this->cal_percentage($totalOpensUniqueDataA,array_sum($finalDeliverCountA));
                }
                
            }else{
                $totalOpensDataB = $automations->openLogs();
                $totalOpensUniqueDataB = $automations->openLogsUnique();
                if($totalOpensDataB > 0){
                    // $totalOpenUniquePercentageB = $this->cal_percentage($totalOpensUniqueDataB,$totalOpensDataB);
                    $totalOpenUniquePercentageB = $this->cal_percentage($totalOpensUniqueDataB,array_sum($finalDeliverCountB));
                }
            }
            
        }


        $totalClicksDataA = 0;
        $totalClicksUniqueDataA = 0;
        $totalClicksDataB = 0;
        $totalClicksUniqueDataB = 0;
        $totalClicksUniquePercentageA =0;
        $totalClicksUniquePercentageB = 0;
        foreach($automation as $key => $automations){
            if($key == 0){
                $totalClicksDataA = $automations->clickLogs();
                $totalClicksUniqueDataA = $automations->clickLogsUnique();

                if($totalClicksDataA > 0){
                    $totalClicksUniquePercentageA = $this->cal_percentage($totalClicksUniqueDataA,$totalClicksDataA);
                }
                
            }else{
                $totalClicksDataB = $automations->clickLogs();
                $totalClicksUniqueDataB = $automations->clickLogsUnique();
                if($totalClicksDataB > 0){
                    $totalClicksUniquePercentageB = $this->cal_percentage($totalClicksUniqueDataB,$totalClicksDataB);
                }
            }
            
        }


        $totalBouncesDataA = 0;
        $totalBouncesUniqueDataA = 0;
        $totalBouncesDataB = 0;
        $totalBouncesUniqueDataB = 0;
        $totalBouncesUniquePercentageA =0;
        $totalBouncesUniquePercentageB = 0;
        foreach($automation as $key => $automations){
           
            if($key == 0){
                $totalBouncesDataA = $automations->bounceLogs();
                $totalBouncesUniqueDataA = $automations->bounceLogsUnique();
                if($totalBouncesDataA > 0){
                    $totalBouncesUniquePercentageA = $this->cal_percentage($totalBouncesUniqueDataA,array_sum($finalDeliverCountA));
                }
                
            }else{
                $totalBouncesDataB = $automations->bounceLogs();
                $totalBouncesUniqueDataB = $automations->bounceLogsUnique();
                if($totalBouncesDataB > 0){
                    $totalBouncesUniquePercentageB = $this->cal_percentage($totalBouncesUniqueDataB,array_sum($finalDeliverCountB));
                }
            }
            
        }
        $segmentAopenstatus = '';
        $segmentBopenstatus = '';
        $segmentAdelivertatus = '';
        $segmentBdelivertatus = '';
        $totalOpensBUnique = 0;
        $segmentAclickstatus = '';
        $segmentBclickstatus = '';

        $segmentABouncestatus = '';
        $segmentBBouncestatus = '';

       // dd($total);
        //dd($total,$automation);
       // dd($totalOpenCountA,$totalOpenDatesA,$totalDeliversCountA,$totalDeliversDatesA,$totalOpenCountB,$totalOpenDatesB,$totalDeliversCountB,$totalDeliversDatesB,array_sum($finalDeliverCountA),$finalDeliverCountB);
           /* echo count($finalOpenCountA);
            echo count($finalDeliverCountA);*/
           // dd(array_sum($finalOpenCountA) * $total/100);
            $openMessage = array_sum($finalOpenCountB) + array_sum($finalOpenCountA);
             if($automation->count()>1){
                $segmentBopenstatus = '<span class="badge badge-info bg-slate badge-big"></span>'. trans("messages.opened"). ' Segment B <br><br>' .'                    
                           <div class="col-md-4">
                           <span class="badge badge-info bg-grey-400 badge-medium custom_badge">';

                $segmentBopenstatus.=  'Total Open - '.$totalOpensDataB. '</span> 
                                 </div>';
                $segmentBopenstatus.=  '<div class="col-md-4">
                               <span class="badge badge-info bg-grey-400 badge-medium custom_badge">Unique Open -'.$totalOpensUniqueDataB. ' </span> 
                                 </div>';                
                $segmentBopenstatus.=  '<div class="col-md-4">
                               <span class="badge badge-info bg-grey-400 badge-medium custom_badge">Open Rate -'.$totalOpenUniquePercentageB. '%   </span> 
                                 </div>';



                $segmentBdelivertatus = '<span class="badge badge-info bg-slate badge-big"></span>'. trans("messages.delivered"). ' Segment B <br><br>' .'                    
                               <div class="col-md-4">
                               <span class="badge badge-info bg-grey-400 badge-medium custom_badge">';

                $segmentBdelivertatus.= ' Total Email - '.$total. '</span> 
                                 </div>';
                $segmentBdelivertatus.=  '<div class="col-md-4">
                               <span class="badge badge-info bg-grey-400 badge-medium custom_badge">Total Delivered - '. array_sum($finalDeliverCountB). '  </span> 
                                 </div>';                


                $segmentBclickstatus = '<span class="badge badge-info bg-slate badge-big"></span> Clicks Segment B <br><br>' .'
                              
                               <div class="col-md-4">
                               <span class="badge badge-info bg-grey-400 badge-medium custom_badge">';

                $segmentBclickstatus.= 'Total Clicks - '.$totalClicksDataB. '  </span> 
                                 </div>';
                $segmentBclickstatus.=  '<div class="col-md-4">
                               <span class="badge badge-info bg-grey-400 badge-medium custom_badge">Unique Clicks - '. $totalClicksUniqueDataB. ' </span> 
                                 </div>';                
                $segmentBclickstatus.=  '<div class="col-md-4">
                               <span class="badge badge-info bg-grey-400 badge-medium custom_badge">Click Rate - '.$totalClicksUniquePercentageB. '% </span> 
                                 </div>';
            //       $segmentBopenstatus = '<span class="badge badge-info bg-slate badge-big"></span>'. trans("messages.opened"). ' Segment B <br><br>' .'
            //               <div class="col-md-4">
            //                <span class="badge badge-info bg-grey-400 badge-medium custom_badge">';

            // $segmentBopenstatus.= trans("messages.open_uniq_per_total", ['total' => $totalOpensDataB]);
            //                  $segmentBopenstatus.=  '</span> </div>'; 
            //    $segmentBopenstatus.= trans("messages.open_uniq_per_total", ['unique_open' => $totalOpensUniqueDataB]);
            //                  $segmentBopenstatus.=  '</span> </div>';    
            //                   $segmentBopenstatus.= trans("messages.open_uniq_per_total", ['uniquepercent' => $totalOpenUniquePercentageB]);
            //                  $segmentBopenstatus.=  '</span> </div>';                

           /*  $segmentBdelivertatus = '<span class="badge badge-info bg-slate badge-big"></span>'. trans("messages.delivered"). ' Segment B <br><br>' .'
                            <span class="badge badge-info bg-grey-400 badge-medium">';

            $segmentBdelivertatus.= trans("messages.deliver_uniq_per_total", ['total' => $total, 'unique_open' => array_sum($finalDeliverCountB)]);*/
                        //     $segmentBdelivertatus.=  '</span>'; 



            // $segmentBclickstatus = '<span class="badge badge-info bg-slate badge-big"></span>'. trans("messages.opened"). ' Segment B <br><br>' .'
            //                 <span class="badge badge-info bg-grey-400 badge-medium">';

            // $segmentBclickstatus.= trans("messages.open_uniq_per_total", ['total' => $totalClicksDataB, 'unique_open' => $totalClicksUniqueDataB,'uniquepercent'=> $totalClicksUniquePercentageB]);
            // $segmentBclickstatus.=  '</span>';

            // $segmentBBouncestatus = '<span class="badge badge-info bg-slate badge-big"></span> Bounce Segment B <br><br>' .'
            //                 <span class="badge badge-info bg-grey-400 badge-medium">';

            // $segmentBBouncestatus.= trans("messages.open_uniq_per_total", ['total' => $totalBouncesDataB, 'unique_open' => $totalBouncesUniqueDataB,'uniquepercent'=> $totalBouncesUniquePercentageB]);

            // $segmentBBouncestatus.=  '</span>';
            $segmentBBouncestatus = '<span class="badge badge-info bg-slate badge-big"></span>Bounce Segment A <br><br>' .'
                          
                           <div class="col-md-4">
                           <span class="badge badge-info bg-grey-400 badge-medium custom_badge">';

            $segmentBBouncestatus.= ' Total Bounce - '.$totalBouncesDataB. ' </span> 
                             </div>';


             }
          /*  $segmentAopenstatus = '<span class="badge badge-info bg-slate badge-big"></span>'. trans("messages.opened").' Segment A <br><br>' .'
                            <span class="badge badge-info bg-grey-400 badge-medium">';

            $segmentAopenstatus.= trans("messages.open_uniq_per_total", ['total' => $totalOpensDataA, 'unique_open' => $totalOpensUniqueDataA,'uniquepercent'=> $totalOpenUniquePercentageA]);
                             $segmentAopenstatus.=  '</span>';*/

               

               $segmentAopenstatus = '<span class="badge badge-info bg-slate badge-big"></span>'. trans("messages.opened"). ' Segment A <br><br>' .'                    
                           <div class="col-md-4">
                           <span class="badge badge-info bg-grey-400 badge-medium custom_badge">';

            $segmentAopenstatus.=  'Total Open - '.$totalOpensDataA. ' </span> 
                             </div>';
            $segmentAopenstatus.=  '<div class="col-md-4">
                           <span class="badge badge-info bg-grey-400 badge-medium custom_badge">Unique Open - '.$totalOpensUniqueDataA. ' </span> 
                             </div>';                
            $segmentAopenstatus.=  '<div class="col-md-4">
                           <span class="badge badge-info bg-grey-400 badge-medium custom_badge">Open Rate - '.$totalOpenUniquePercentageA. '%   </span> 
                             </div>';                                   
                

          /*   $segmentAdelivertatus = '<span class="badge badge-info bg-slate badge-big"></span>'. trans("messages.delivered"). ' Segment A <br><br>' .'
                            <span class="badge badge-info bg-grey-400 badge-medium">';

            $segmentAdelivertatus.= trans("messages.deliver_uniq_per_total", ['total' => $total, 'unique_open' => array_sum($finalDeliverCountA)]);
            $segmentAdelivertatus.=  '</span>'; */

            $segmentAdelivertatus = '<span class="badge badge-info bg-slate badge-big"></span>'. trans("messages.delivered"). ' Segment A <br><br>' .'                    
                           <div class="col-md-4">
                           <span class="badge badge-info bg-grey-400 badge-medium custom_badge">';

            $segmentAdelivertatus.= 'Total Contact - '.$total. ' </span> 
                             </div>';
            $segmentAdelivertatus.=  '<div class="col-md-4">
                           <span class="badge badge-info bg-grey-400 badge-medium custom_badge">Total Delivered - '. array_sum($finalDeliverCountA). '  </span> 
                             </div>';                
          /*  $segmentAdelivertatus.=  '<div class="col-md-4">
                           <span class="badge badge-info bg-grey-400 badge-medium custom_badge">'.trans("messages.percentage", ['percentage' => $totalOpenUniquePercentageA]). '</span> 
                             </div>'; */  


            //dd($totalClicksUniqueDataA,$totalClicksDataA);
            $segmentAclickstatus = '<span class="badge badge-info bg-slate badge-big"></span>Clicks Segment A <br><br>' .'
                          
                           <div class="col-md-4">
                           <span class="badge badge-info bg-grey-400 badge-medium custom_badge">';

            $segmentAclickstatus.= 'Total Clicks - '.$totalClicksDataA. '  </span> 
                             </div>';
            $segmentAclickstatus.=  '<div class="col-md-4">
                           <span class="badge badge-info bg-grey-400 badge-medium custom_badge">Unique Clicks - '. $totalClicksUniqueDataA. ' </span> 
                             </div>';                
            // $segmentAclickstatus.=  '<div class="col-md-4">
            //                <span class="badge badge-info bg-grey-400 badge-medium custom_badge">Click Rate - '.$totalClicksUniquePercentageA. '%  </span> 
            //                  </div>';                    
            //$segmentAdelivertatus.=  '</span>'; hide by himanshu


            
            
         /*   $segmentBclickstatus = '<span class="badge badge-info bg-slate badge-big"></span> Segment B <br><br>' .'
                          
                           <div class="col-md-4">
                           <span class="badge badge-info bg-grey-400 badge-medium custom_badge">';

            $segmentBclickstatus.= trans("messages.total_clicked", ['total' => $totalClicksDataB]). '</span> 
                             </div>';
            $segmentBclickstatus.=  '<div class="col-md-4">
                           <span class="badge badge-info bg-grey-400 badge-medium custom_badge">'.trans("messages.unique_clicked", ['unique_clicked' => $totalClicksUniqueDataB]). '</span> 
                             </div>';                
            $segmentBclickstatus.=  '<div class="col-md-4">
                           <span class="badge badge-info bg-grey-400 badge-medium custom_badge">'.trans("messages.percentage", ['percentage' => $totalClicksUniquePercentageB]). '</span> 
                             </div>';                    	
                             */

            $segmentABouncestatus = '<span class="badge badge-info bg-slate badge-big"></span>Bounce Segment A <br><br>' .'
                          
                           <div class="col-md-4">
                           <span class="badge badge-info bg-grey-400 badge-medium custom_badge">';

            $segmentABouncestatus.= ' Total Bounce - '.@$finalBounceCountA[1]. '</span> 
                             </div>';
            // $segmentABouncestatus.=  '<div class="col-md-4">
            //                <span class="badge badge-info bg-grey-400 badge-medium custom_badge">'. $totalBouncesUniqueDataA. ' Unique Bounce</span> 
            //                  </div>';                
            // $segmentABouncestatus.=  '<div class="col-md-4">
            //                <span class="badge badge-info bg-grey-400 badge-medium custom_badge">'.$totalBouncesUniquePercentageA. '% Unique </span> 
            //                  </div>';   
           	// $segmentABouncestatus = '<span class="badge badge-info bg-slate badge-big"></span>Bounce Segment A <br><br>' .'
            //                 <span class="badge badge-info bg-grey-400 badge-medium">';

            // $segmentABouncestatus.= trans("messages.open_uniq_per_total", ['total' => $totalBouncesDataA, 'unique_open' => $totalBouncesUniqueDataA,'uniquepercent'=> $totalBouncesUniquePercentageA]);
            // $segmentABouncestatus.=  '</span>';

            
                             
                               



            $openMessageData = '<span class="badge badge-info bg-slate badge-big"></span>'.trans("messages.opened").'<br><br>
                            <span class="badge badge-info bg-grey-400 badge-medium">'.trans("messages.open_uniq_per_total", ['total' => $openMessage , 'unique_open' => $AutomationList->mailList->openUniqRate(),'uniquepercent'=> $AutomationList->mailList->openUniqRate()]).'
                            </span>';
                            $insight1 = array();
                            $alphabet = 'A';
                            foreach($automation as $automationssss){
                                
                                $insight = $automationssss->getInsight();
                                if(count($insight)>1){
                                    $insight1[] = $alphabet;
                                }
                                $alphabet++;
                            }
                                                        
                 //DD($segmentAclickstatus);       
            $result = array(
            	'totalOpenCountA'=>$finalOpenCountA,
            	'totalOpenDatesA'=> $newOperDateArray,
            	'totalDeliversCountA'=>$finalDeliverCountA,
            	'totalDeliversDatesA'=>$newDeliverDateArray,
            	'totalOpenCountB'=>$finalOpenCountB,
            	'totalOpenDatesB'=>$totalOpenDatesB,
            	'totalDeliversCountB'=>$finalDeliverCountB,
            	'totalDeliversDatesB'=>$totalDeliversDatesB,
            	'segmentAopenstatus'=>$segmentAopenstatus,
            	'segmentBopenstatus'=>$segmentBopenstatus,
            	'segmentAdelivertatus'=>$segmentAdelivertatus,
            	'segmentBdelivertatus'=>$segmentBdelivertatus,
            	'openMessageData'=>$openMessageData,
            	'totalClickCountA'=>$finalClickCountA,
            	'totalClickDatesA'=> $newClickDateArray,
            	'totalClickCountB'=>$finalClickCountB,
            	'totalClickDatesB'=>$totalClickDatesB,
            	'segmentAclickstatus'=>$segmentAclickstatus,
            	'segmentBclickstatus'=>$segmentBclickstatus, 
            	'totalBounceCountA'=>$finalBounceCountA,
            	'totalBounceDatesA'=> $newBounceDateArray,
            	'totalBounceCountB'=>$finalBounceCountB,
            	'totalBounceDatesB'=>$totalBounceDatesB,
            	'segmentABouncesstatus'=>$segmentABouncestatus,
            	'segmentBbouncesstatus'=>$segmentBBouncestatus,
                'insight1' => $insight1,
                'automation' => $automation,
            );

        return json_encode($result);
    }

    public function cal_percentage($num_amount, $num_total) {
      $count1 = $num_amount / $num_total;
      $count2 = $count1 * 100;
      $count = number_format($count2, 0);
      return $count;
    }
    public function graphLogin(){
        // $graphMailer = new GraphMailer('f8cdef31-a31e-4b4a-93e4-5f571e91255a','22974d00-b5e3-46e1-a23a-ad5e2e2b8ac1','65_7Q~G9Kcsc5-M23E9O93bsN5bvmtQQ2F7le');

        // $messages = $graphMailer->getMessages('helpdesk@contoso.com');
        // echo '<pre>';
        // print_r($messages);
        // echo '</pre>';

        $oauthClient = new \League\OAuth2\Client\Provider\GenericProvider([
          'clientId'                => config('azure.appId'),
          'clientSecret'            => config('azure.appSecret'),
          'redirectUri'             => config('azure.redirectUri'),
          'urlAuthorize'            => config('azure.authority').config('azure.authorizeEndpoint'),
          'urlAccessToken'          => config('azure.authority').config('azure.tokenEndpoint'),
          'urlResourceOwnerDetails' => '',
          'scopes'                  => config('azure.scopes')
        ]);

        $authUrl = $oauthClient->getAuthorizationUrl();
        
        // Save client state so we can validate in callback
        session(['oauthState' => $oauthClient->getState()]);

        // Redirect to AAD signin page
        return redirect()->away($authUrl);
    }

    public function saveResponseGraph(Request $request){

        // Validate state
        $expectedState = session('oauthState');
        $request->session()->forget('oauthState');
        $providedState = $request->query('state');

        if (!isset($expectedState)) {
          // If there is no expected state in the session,
          // do nothing and redirect to the home page.
          return redirect('/');
        }

        if (!isset($providedState) || $expectedState != $providedState) {
          return redirect('/')
            ->with('error', 'Invalid auth state')
            ->with('errorDetail', 'The provided auth state did not match the expected value');
        }

        // Authorization code should be in the "code" query param
        $authCode = $request->query('code');
        if (isset($authCode)) {
          // Initialize the OAuth client
          $oauthClient = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId'                => config('azure.appId'),
            'clientSecret'            => config('azure.appSecret'),
            'redirectUri'             => config('azure.redirectUri'),
            'urlAuthorize'            => config('azure.authority').config('azure.authorizeEndpoint'),
            'urlAccessToken'          => config('azure.authority').config('azure.tokenEndpoint'),
            'urlResourceOwnerDetails' => '',
            'scopes'                  => config('azure.scopes')
          ]);

            // Make the token request
            $accessToken = $oauthClient->getAccessToken('authorization_code', [
              'code' => $authCode
            ]);
            // var_dump($accessToken->getToken());
            $graph = new Graph();
              $graph->setAccessToken($accessToken->getToken());

              $user = $graph->createRequest('GET', '/me?$select=displayName,mail,mailboxSettings,userPrincipalName')
                ->setReturnType(Model\User::class)
                ->execute();
                
           $checkAlready = \Acelle\Model\SendingServer::where('default_from_email',$user->getUserPrincipalName())->where('customer_id',$request->user()->customer->id)->where('type','Microsoft')->get();
           if(count($checkAlready)>0){

           }else{
                $sending_server = new \Acelle\Model\SendingServer();
                $sending_server->customer_id= $request->user()->customer->id;
                $sending_server->name = $user->getUserPrincipalName();
                $sending_server->type = 'Microsoft';
                $sending_server->default_from_email = $user->getUserPrincipalName();
                $sending_server->quota_value = 1000;
                $sending_server->quota_base = 1;
                $sending_server->quota_unit = 'hour';
                $sending_server->status = 'active';
                $sending_server->token_mail = $accessToken->getToken();
                $sending_server->api_secret_key = $accessToken->getRefreshToken();
                //$sending_server->token_mail = json_encode($accessToken);
                $sending_server->save();
                $sender = new \Acelle\Model\Sender();
                $sender->customer_id= $request->user()->customer->id;
                $sender->name = 'Microsoft '.$user->getUserPrincipalName();
                $sender->email = $user->getUserPrincipalName();
                $sender->status = 'verified';
                $sender->sending_server_id = $sending_server->id;
           }     
            
        //    return  $accessToken->getToken();
            // return redirect('/');
            // TEMPORARY FOR TESTING!
            return redirect('/')
              ->with('error', 'Access token received')
              ->with('errorDetail', $accessToken->getToken());

        }

        // return redirect('/')
        //   ->with('error', $request->query('error'))
        //   ->with('errorDetail', $request->query('error_description'));
        // $token = \LaravelGmail::makeToken();
        
        // if(count($checkAlready)>0){

        // }else{
        //     //dd(\LaravelGmail::user());
        //     $sending_server = new \Acelle\Model\SendingServer();
        //     $sending_server->customer_id= $request->user()->customer->id;
        //     $sending_server->name = \LaravelGmail::user();
        //     $sending_server->type = 'Gmail';
        //     $sending_server->default_from_email = $token['email'];
        //     $sending_server->quota_value = 1000;
        //     $sending_server->quota_base = 1;
        //     $sending_server->quota_unit = 'hour';
        //     $sending_server->status = 'active';
        //     $sending_server->aws_access_key_id = $token['access_token'];
        //     $sending_server->aws_secret_access_key = $token['refresh_token'];
        //     $sending_server->token_mail = json_encode($token);
        //     $sending_server->save();
        //     $sender = new \Acelle\Model\Sender();
        //     $sender->customer_id= $request->user()->customer->id;
        //     $sender->name = 'Gmail '.$token['email'];
        //     $sender->email = $token['email'];
        //     $sender->status = 'verified';
        //     $sender->sending_server_id = $sending_server->id;
            
        //     unlink(storage_path('app/gmail/tokens/gmail-json-'.Auth::user()->id.'.json'));
        //     // $messages = \LaravelGmail::message();
        //     // var_dump($message);
        //     // exit;
        //     // foreach ( $messages as $message ) {
        //     //     $body = $message->getHtmlBody();
        //     //     $subject = $message->getSubject();
        //     //     var_dump($body);
        //     //     var_dump($subject);
        //     //     exit;
        //     // }
        // }
        
        return redirect('/');
        
    }


    public function addSignature(Request $request){

        $customerUid = $request->user()->customer->uid;
        $customer = Customer::findByUid($customerUid);
        $customer->footer_text = $request->footer_text;
       //dd($request,$customer);
        $customer->save();
        //dd($customer);
        return redirect()->back();
    }
public function upload(Request $request){
    //dd($request->file('file'));
        $fileName=$request->file('file')->getClientOriginalName();
        
        $path=$request->file('file')->storeAs('uploads', $fileName,'public');
      //  dd($path);
        return response()->json(['location'=> url('/')."/storage/$path"]); 
        
        /*$imgpath = request()->file('file')->store('uploads', 'public'); 
        return response()->json(['location' => "/storage/$imgpath"]);*/

    }

    public function createOpenSturcture(Request $request){
        $automation = Automation2::findByUid($request->uid);
        $automationList = AutomationList::findOrFail($automation->main_id);
        $mail_list = \Acelle\Model\MailList::where('id',$automationList->mail_list_id)->first();
             $subscriberData = \Acelle\Model\Subscriber::where('mail_list_id',$mail_list->id)->get();
             foreach($subscriberData as $value){
                    $subscriberDatas[] = $value->email;
                    
                } 
            $newId = rand(100000000,999999999);
            $newWaitId = rand(100000000,999999999);
            
            
            $data = json_decode($automation->data,true);
            
            $countData = count($data) -1;
            foreach($data as $key => $datas){
                $newData[] = $datas; 
                if($datas['id'] == $request->elementId){
                    $newData[$key]['child'] = $newId;
                }
            }
            $email_link = $newData[$countData]['options']['email_uid'];

            $optionsArray = array(
                        "key"=> "condition",
                          "type"=> 'open',
                          "email_uid"=> $email_link,
                          "email"=> $email_link,
                          "template"=> "true",
                          'subscribers' =>  $subscriberDatas,
                          "wait" => "1 day",
                    );
            $newData[count($data)] = array(
                        "id"=>(int)$newId,
                        "title"=> "On previous mail open",
                        "type"=> 'ElementCondition',
                        'segmentNumber' => 1,
                        "child"=> null,
                        "childYes" => null,
                        "childNo" => null,
                        "options"=> $optionsArray,
                        "last_executed"=> null,
                        "evaluationResult"=> null,
                        
                    );
            $automation->data = json_encode($newData,true);
            $automation->save();
            return response()->json(['status'=> true]);

    }

    public function changeSegment($uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        $AutomationList = AutomationList::findOrFail($automation->main_id);
        $automations = Automation2::where('main_id',$AutomationList->id)->get();
        $automationss = Automation2::where('main_id',$AutomationList->id)->first();
        $diffAutomation = '';
        $segmentName = '';
        foreach($automations as $test){
            if($automation->id != $test->id){
                $diffAutomation = $test->uid;
            }
            if($automationss->id == $automation->id){
                $segmentName = 'B';
            }else{
                $segmentName = 'A';
            }
        }
        //$diffAutomation = 
        //$automation->updateCacheInBackground();
        //dd($automation);
        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }
        
        return view('automation2.edit', [
            'AutomationList' => $AutomationList,
            'automations' => $automations,
            'automation' => $automation,
            'diffAutomation' => $diffAutomation,
            'segmentName' => $segmentName
        ]);
    }

    public function addSMTP(Request $request){
        $automation = AutomationList::findByUid($request->uid);

        $automation->smtp_server_id  = $request->mail_server;
        $automation->step_no = 2;
        // save
        $automation->save();
        $automationData = Automation2::where('main_id',$automation->id)->get();
        if(count($automationData)>0){
            foreach($automationData as $automations){
                
                $automations->smtp_server_id  = $request->mail_server;
                // save
                $automations->save();
            }    
        }

        return redirect('automation/step2/'.$automation->uid);
    }

    public function finishStep($uid){
        $automation = AutomationList::findByUid($uid);
        $subscriberData = \Acelle\Model\Subscriber::where('mail_list_id',$automation->mail_list_id)->get();
        if($automation->smtp_server_id == '' || $automation->mail_list_id == '' || count($subscriberData) == 0){
            

            //if(count($subscriberData) == 0){
                return redirect()->back();
            //}
            
        }
        $automation->step_no = 3;
        $automation->save();
        return redirect(action('Automation2Controller@edit', ['uid' => $automation->uid]));
    }


    public function MailListUpdate($uid,$automationUid){
        $mail_list = \Acelle\Model\MailList::findByUid($uid);
        //dd($mail_list);
        $automation = AutomationList::findByUid($automationUid);
        $automation->mail_list_id = $mail_list->id;
        $automation->save();
        $automationData = Automation2::where('main_id',$automation->id)->get();
        if(count($automationData)>0){
            foreach($automationData as $automations){
                $automations->mail_list_id = $mail_list->id;
                $automations->save();
            }
        }

    }

    public function declineBounce(){
        $sql = "update bounce_handeler_notification set status = 1 where user_id = ".Auth::user()->id;
        
        DB::statement($sql);
        return true;
    }

}
