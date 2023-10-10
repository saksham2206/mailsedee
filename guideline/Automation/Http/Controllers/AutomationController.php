<?php

namespace Modules\Automation\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Acelle\Model\Automation2;
use Acelle\Model\Template;
use DB;
use Acelle\Events\MailListSubscription;
use Illuminate\Support\Facades\Session;


class AutomationController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        return view('automation::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(Request $request)
    {
        $customer = $request->user()->customer;
        
        // init automation
        $automation = new Automation2([
            'name' => Date('d M Y h:i'),
        ]);
        $automation->status = Automation2::STATUS_INACTIVE;
        
        
        // authorize
        // if (\Gate::denies('create', $automation)) {
        //     return $this->noMoreItem();
        // }

        return view('automation::create',[
            'automation' => $automation,
        ]);
    }

    public function step1Store(Request $request){
        $customer = $request->user()->customer;
        $automationNameField = $request->input('name');
        $from_name = $request->input('from_name');
        $sending_server = $request->input('mail_server');
        //dd($sending_server);
        $sending_server_details = \Acelle\Model\SendingServer::where('id',$sending_server)->first();
        
        $email = $sending_server_details->default_from_email;


        $customer = $request->user()->customer;
        
        $contact = \Acelle\Model\Contact::create([
            'email' => $email
        ]);
        $contact->save();
        
        $list = new \Acelle\Model\MailList();
        $ada = array([
            'customer_id'=> $customer->id,
            'contact_id' => $contact->id, 
        ]);

        //exit;
        $list->fill($ada);
        $list->customer_id = $customer->id;
        $list->contact_id = $contact->id;
        $list->name = $automationNameField;
        $list->from_name = $from_name;

        // var_dump($list);
        // exit;
        $list->save();
        // init automation
        $automation = Automation2::create([
            'name' => $automationNameField,
            'customer_id' => $customer->id,
            'mail_list_id' => $list->id,
            'time_zone' => null,
            'status' => 'active',
            'smtp_server_id' => $sending_server_details->id,
            'status' => Automation2::STATUS_INACTIVE,
            'data' => '[{"title":"Click to choose a trigger","id":"trigger","type":"ElementTrigger","options":{"init":"false", "key": ""}}]',
        ]);
        
        
        // authorize
        // if (\Gate::denies('create', $automation)) {
        //     return $this->noMoreItem();
        // }
        Session::put('automation',$automation);
        return redirect('automation/step2/'.$automation->uid)->with( ['automation' => $automation] );
    }

    public function Step2($automationUid)
    {
        $automation = Automation2::findByUid($automationUid);
        return view('automation::createste2',[
            'automation' => $automation,
        ]);
        
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
        
        /*echo "<pre>";
        var_dump($request->all());
        exit;
*/        $optionsArray = array(
                "init"=> true,
                  "type"=> "datetime",
                  "date"=> date('Y-m-d'),
                  "at" => date('h:i A'),
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

        for($i = 1; $i<=$request->segmentNumber; $i++){

            for ($j=0; $j <count($request->child[1]) ; $j++) { 
                $optionsArray = array(
                    "init"=> "true",
                      "email_uid"=> $request->template_uid[1][$j],
                      "template"=> "true",
                      
                );
                if($j == count($request->child[1])-1){
                    $child = null;
                }else{
                    $child = (int)$request->id[1][$j+1];
                }

                $data[] = array(
                    "id"=>(int)$request->id[1][$j],
                    "title"=> "Send email `Welcom",
                    "type"=> 'ElementAction',
                    "child"=> $child,
                    "options"=> $optionsArray,
                    "last_executed"=> null,
                    "evaluationResult"=> null,
                    
                );
            }

            

        }
        /*print_r(json_encode($data));
        exit;*/
        $automation = Automation2::findByUid($request->input('automation_uid'));
        $automation->data = json_encode($data);
        $automation->save();
        return redirect('automation');

        var_dump(json_encode($data));
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

    public function uploadCsvList(){
        $fileData = '';
        if(isset($_FILES['file']['name'][0]))
        {
          foreach($_FILES['file']['name'] as $keys => $values)
          {
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
    }

    public function csvToArray($filename = '', $delimiter = ',')
    {
        if (!file_exists($filename) || !is_readable($filename))
            return false;

        $header = null;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== false)
        {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false)
            {

                if (!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }
        $finalData['header'] = $header;
        $finalData['data'] = $data; 

        return $finalData;
    }

    public function importContacts(Request $request){
        $emailField = $request->input('EmailField');
        $NameField = $request->input('NameField');
        $keyName = $request->input('keyName');
        $valueName = $request->input('valueName');
        $wholecsvdata = $request->input('wholecsvdata');
        $csvToArray = json_decode($wholecsvdata);
        $saveData = array();
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
        if(strtolower($keyName) == 'phone' || strtolower($keyName) == 'mobile no'){
            $phonefields = \Acelle\Model\Field::create([
                'mail_list_id' => $list->id,
                'type' => 'number',
                'label' => 'Phone',
                'required' => '0',
                'tag' => 'PHONE',

            ]);
            $phonefields->save();
        }else{
            $phonefields = \Acelle\Model\Field::create([
                'mail_list_id' => $list->id,
                'type' => 'text',
                'label' => $keyName,
                'required' => '0',
                'tag' => strtoupper($keyName),

            ]);
            $phonefields->save();
        }
        $field = "";
        foreach($csvToArray[0]->data as $key=> $value){
            $values = (array)$value;
            $saveData[$emailField] = $values[$emailField];
            $saveData[$NameField] = $values[$NameField];
            $saveData[$keyName] = $values[$keyName];
            
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
                'field_id' => $phonefields->id,
                'value' => $values[$keyName],
            ]);
            $subscriberFeild->save();

            event(new \Acelle\Events\MailListUpdated($subscriber->mailList));
            MailListSubscription::dispatch($subscriber);

        }

        
        //$customer = $request->user()->customer;
        






    }

    public function createSequenceTemplate(Request $request)
    {
        $customer = $request->user()->customer;
        
        $type = $request->type;
        $sequenceId = $request->id;

        return view('automation::email.create',compact('type','sequenceId'));
    }

    public function storeTemplate(Request $request)
    {
        // authorize
        if (!$request->user()->customer->can('create', Template::class)) {
            return $this->notAuthorized();
        }

        $template = Template::saveTemplate($request); 

        $templateData = Template::findByUid($template->uid);
        $type = $request->type;
        $sequenceId = $request->sequenceId;
    
        return response()->json(['status'=> true, 'data' => $templateData, 'type'=> $type,'sequenceId'=> $sequenceId]);
    }


}
