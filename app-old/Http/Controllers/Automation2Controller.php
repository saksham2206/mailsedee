<?php

namespace Acelle\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use Acelle\Http\Requests;
use Acelle\Model\Automation2;
use Acelle\Model\AutomationList;
use Acelle\Model\MailList;
use Acelle\Model\Email;
use Acelle\Model\Attachment;
use Acelle\Model\Template;
use Acelle\Model\Subscriber;
use Acelle\Model\BounceHandler;
use Illuminate\Support\Facades\Storage;
use Acelle\Model\TemplateCategory;
//use Acelle\Library\Log;
use Acelle\Library\Log as MailLog;
use Acelle\Library\StringHelper;
class Automation2Controller extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        return view('automation2.index');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function listing(Request $request)
    {

        
        $automations = $request->user()->customer->AutomationList()
            ->search($request->keyword)
            ->orderBy($request->sort_order, $request->sort_direction)
            ->paginate($request->per_page);
        return view('automation2._list', [
            'automations' => $automations,
        ]);
    }
    
    /**
     * Creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $customer = $request->user()->customer;
        
        // init automation
        $automation = new Automation2([
            'name' => trans('messages.automation.untitled'),
        ]);
        $automation->status = Automation2::STATUS_INACTIVE;
        
        // authorize
        // if (\Gate::denies('create', $automation)) {
        //     return $this->noMoreItem();
        // }
        // var_dump("hello");
        // exit;
        // saving
        if ($request->isMethod('post')) {
            // fill before save
            $automation->fillRequest($request);
            
            // make validator
            $validator = Validator::make($request->all(), $automation->rules());
            
            // redirect if fails
            if ($validator->fails()) {
                return response()->view('automation2.create', [
                    'automation' => $automation,
                    'errors' => $validator->errors(),
                ], 400);
            }
            
            // pass validation and save
            $automation->mail_list_id = MailList::findByUid($request->mail_list_uid)->id;
            $automation->customer_id = $customer->id;
            $automation->data = '[{"title":"Click to choose a trigger","id":"trigger","type":"ElementTrigger","options":{"init":"false", "key": ""}}]';
            $automation->save();
            
            return response()->json([
                'status' => 'success',
                'message' => trans('messages.automation.created.redirecting'),
                'url' => url('automation/create', ['uid' => $automation->uid])
            ], 201);
        }
        
        return view('automation2.create', [
            'automation' => $automation,
        ]);
    }
    
    /**
     * Update automation.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $uid)
    {
        $customer = $request->user()->customer;
        
        // find automation
        $automation = AutomationList::findByUid($uid);
        
        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }
        
        // fill before save
        $automation->fillRequest($request);
        $messages = array(
            'name.required' => 'Please mention your automation name.',
            'mail_list_uid.required' => 'Please choose a list of audiences.'
        );
        // make validator
        $validator = Validator::make($request->all(), $automation->rules(),$messages);
            
        // redirect if fails
        if ($validator->fails()) {
            return response()->view('automation2.settings', [
                'automation' => $automation,
                'errors' => $validator->errors(),
            ], 400);
        }
            
        // pass validation and save
        $automation->updateMailList(MailList::findByUid($request->mail_list_uid));
        $automation->smtp_server_id  = $request->mail_server;
        // save
        $automation->save();
        $automationData = Automation2::where('main_id',$automation->id)->get();
        if(count($automationData)>0){
            foreach($automationData as $automations){
                $automations->updateMailList(MailList::findByUid($request->mail_list_uid));
                $automations->smtp_server_id  = $request->mail_server;
                // save
                $automations->save();
            }    
        } 
            
        return response()->json([
            'status' => 'success',
            'message' => trans('messages.automation.updated'),
        ], 201);
    }
    
    /**
     * Update automation.
     *
     * @return \Illuminate\Http\Response
     */
    public function saveData(Request $request, $uid)
    {
        // find automation
        $automation = Automation2::findByUid($uid);

        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }
            // dd($request);
        if ($request->resetTrigger) {
            $automation->saveDataAndResetTriggers($request->data);
        } else {
            $automation->saveData($request->data);
        }
    }
    
    /**
     * Creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $uid)
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
        if($AutomationList->step_no == 1){
            
            // page that is rendered at: https://app.sende.io/automation2/{uid}/edit
            return view('automation::create', [
                'AutomationList' => $AutomationList,
                'automations' => $automations,
                'automation' => $automation,
                'diffAutomation' => $diffAutomation,
                'segmentName' => $segmentName
            ]);
        }else if($AutomationList->step_no == 2){
            return redirect('automation/step2/'.$AutomationList->uid);
        }else{
          
            return view('automation2.edit', [
                'AutomationList' => $AutomationList,
                'automations' => $automations,
                'automation' => $automation,
                'diffAutomation' => $diffAutomation,
                'segmentName' => $segmentName
            ]);
        }
        
        
    }
    
    /**
     * Automation settings in sidebar.
     *
     * @return \Illuminate\Http\Response
     */
    public function settings(Request $request, $uid)
    {
        // init automation
        $automation = AutomationList::findByUid($uid);
        
        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }
        
        return view('automation2.settings', [
            'automation' => $automation,
        ]);
    }
    
    /**
     * Select trigger type popup.
     *
     * @return \Illuminate\Http\Response
     */
    public function triggerSelectPupop(Request $request, $uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);

        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }
        
        $types = [
            'welcome-new-subscriber',
            'say-happy-birthday',
            'subscriber-added-date',
            'specific-date',
            'say-goodbye-subscriber',
            'api-3-0',
            'weekly-recurring',
            'monthly-recurring',
        ];

        if (config('custom.woo')) {
            $types[] = 'woo-abandoned-cart';
        }
        
        return view('automation2.triggerSelectPupop', [
            'types' => $types,
            'automation' => $automation,
            'trigger' => $automation->getTrigger(),
        ]);
    }
    
    /**
     * Select trigger type confirm.
     *
     * @return \Illuminate\Http\Response
     */
    public function triggerSelectConfirm(Request $request, $uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        $rules = $this->triggerRules()[$request->key];
        
        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }
        
        return view('automation2.triggerSelectConfirm', [
            'key' => $request->key,
            'automation' => $automation,
            'trigger' => $automation->getTrigger(),
            'rules' => $rules,
        ]);
    }

    /**
     * Select trigger type.
     *
     * @return array
     */
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

    /**
     * Validate trigger.
     *
     * @return \Illuminate\Http\Response
     */
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
     * Select trigger type.
     *
     * @return \Illuminate\Http\Response
     */
    public function triggerSelect(Request $request, $uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        
        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }

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
        
        return response()->json([
            'status' => 'success',
            'message' => trans('messages.automation.trigger.added'),
            'title' => trans('messages.automation.trigger.title', [
                'title' => trans('messages.automation.trigger.tree.' . $request->options["key"])
            ]),
            'options' => $request->options,
            'rules' => $this->triggerRules()[$request->options['key']],
        ]);
    }
    
    /**
     * Select action type popup.
     *
     * @return \Illuminate\Http\Response
     */
    public function actionSelectPupop(Request $request, $uid)
    {

        // init automation
        $automation = Automation2::findByUid($uid);
        $AutomationList = AutomationList::findByUid($request->AutolmationListUid);
        
        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }
        
        $types = [
            'send-an-email',
            'wait',
            'condition',
        ];

        if (config('custom.woo')) {
            $types[] = 'operation';
        }
        
        return view('automation2.actionSelectPupop', [
            'types' => $types,
            'automation' => $automation,
            'hasChildren' => $request->hasChildren,
            'eleid' =>$request->eleId,
            'AutomationList' => $AutomationList,
        ]);
    }
    
    /**
     * Select action type confirm.
     *
     * @return \Illuminate\Http\Response
     */
    public function actionSelectConfirm(Request $request, $uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        
        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }
        
        return view('automation2.actionSelectConfirm', [
            'key' => $request->key,
            'automation' => $automation,
            'element' => $automation->getElement(),
        ]);
    }

    /**
     * Select action type confirm.
     *
     * @return \Illuminate\Http\Response
     */
    public function conditionSetting(Request $request, $uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        
        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }
        
        return view('automation2.condition.setting', [
            'automation' => $automation,
            'element' => $automation->getElement($request->element_id),
        ]);
    }
    
    /**
     * Select trigger type.
     *
     * @return \Illuminate\Http\Response
     */
    public function actionSelect(Request $request, $uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        
        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }
        
        if ($request->key == 'wait') {
            $delayOptions = $automation->getDelayOptions();
            $parts = explode(' ', $request->time);
            $title = trans('messages.time.wait_for') . ' ' . $parts[0] . ' ' . trans_choice('messages.time.' . $parts[1], $parts[0]);

            foreach ($delayOptions as $deplayOption) {
                if ($deplayOption['value'] == $request->time) {
                    $title = trans('messages.automation.wait.delay.' . $request->time);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => trans('messages.automation.action.added'),
                'title' => $title,
                'options' => [
                    'key' => $request->key,
                    'time' => $request->time,
                ],
            ]);
        } elseif ($request->key == 'condition') {
            if ($request->type == 'open') {
                return response()->json([
                    'status' => 'success',
                    'message' => trans('messages.automation.action.added'),
                    'title' => trans('messages.automation.action.condition.read_email.title'),
                    'options' => [
                        'key' => $request->key,
                        'type' => $request->type,
                        'email' => empty($request->email) ? null : $request->email,
                    ],
                ]);
            } elseif ($request->type == 'click') {
                return response()->json([
                    'status' => 'success',
                    'message' => trans('messages.automation.action.added'),
                    'title' => trans('messages.automation.action.condition.click_link.title'),
                    'options' => [
                        'key' => $request->key,
                        'type' => $request->type,
                        'email_link' => empty($request->email_link) ? null : $request->email_link,
                    ],
                ]);
            } elseif ($request->type == 'cart_buy_anything') {
                return response()->json([
                    'status' => 'success',
                    'message' => trans('messages.automation.action.updated'),
                    'title' => trans('messages.automation.action.condition.cart_buy_anything.title'),
                    'options' => [
                        'key' => $request->key,
                        'type' => $request->type,
                    ],
                ]);
            } elseif ($request->type == 'cart_buy_item') {
                return response()->json([
                    'status' => 'success',
                    'message' => trans('messages.automation.action.updated'),
                    'title' => trans('messages.automation.action.condition.cart_buy_item.title', [
                        'item' => $request->item_title,
                    ]),
                    'options' => [
                        'key' => $request->key,
                        'type' => $request->type,
                        'item_id' => $request->item_id,
                        'item_title' => $request->item_title,
                    ],
                ]);
            }
        } else {
            return response()->json([
                'status' => 'success',
                'message' => trans('messages.automation.action.added'),
                'title' => trans('messages.automation.action.title', [
                    'title' => trans('messages.automation.action.' . $request->key)
                ]),
                'options' => [
                    'key' => $request->key,
                    'after' => $request->after,
                ],
            ]);
        }
    }
    
    /**
     * Edit trigger.
     *
     * @return \Illuminate\Http\Response
     */
    public function triggerEdit(Request $request, $uid)
    {
        // init automation
        // if($request->isMethod('post')){
        //     $automations = AutomationList::findByUid($uid);
        //     $automation = Automation2::where('main_id',$automations->id);
            
        // }else{
            $automation = Automation2::findByUid($uid);
            $automations = AutomationList::findOrFail($automation->main_id);
        //}
       
        $rules = $this->triggerRules()[$request->key];
        
        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }
        
        if ($request->isMethod('post')) {
            list($validator, $result) = $this->vaidateTrigger($request, $request->options['key']);
          //  dd($result);
            // redirect if fails
            if (!$result) {
              
                return response()->view('automation2.triggerEdit', [
                    'key' => $request->options['key'],
                    'automation' => $automations,
                    'trigger' => $automation->getTrigger(),
                    'rules' => $this->triggerRules()[$request->options['key']],
                    'errors' => $validator->errors(),
                ], 400);
            }

            $data = json_decode($automation->data);
            $data[0]->options->date = $request->options['date'];
            $data[0]->options->at = $request->options['at'];
            $automation->data = json_encode($data);
            $automation->save();
            return response()->json([
                'status' => 'success',
                'message' => trans('messages.automation.trigger.updated'),
                'title' => trans('messages.automation.trigger.title', [
                    'title' => trans('messages.automation.trigger.tree.' . $request->options["key"])
                ]),
                'options' => $request->options,
            ]);
        }
        
        return view('automation2.triggerEdit', [
            'key' => $request->key,
            'automation' => $automation,
            'automations' => $automations,
            'trigger' => $automation->getTrigger(),
            'rules' => $rules,
        ]);
    }
    
    /**
     * Edit action.
     *
     * @return \Illuminate\Http\Response
     */
    public function actionEdit(Request $request, $uid)
    {
        // init automation

        $automation = Automation2::findByUid($uid);
        $automations = AutomationList::findOrFail($automation->main_id);
       
        //$automations = Automation2::where('main_id',$automation->id);
        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }
        
        // saving
        if ($request->isMethod('post')) {
            if ($request->key == 'wait') {
                $delayOptions = $automation->getDelayOptions();
                $parts = explode(' ', $request->time);
                $title = trans('messages.time.wait_for') . ' ' . $parts[0] . ' ' . trans_choice('messages.time.' . $parts[1], $parts[0]);

                foreach ($delayOptions as $deplayOption) {
                    if ($deplayOption['value'] == $request->time) {
                        $title = trans('messages.automation.wait.delay.' . $request->time);
                    }
                }

                return response()->json([
                    'status' => 'success',
                    'message' => trans('messages.automation.action.updated'),
                    'title' => $title,
                    'options' => [
                        'key' => $request->key,
                        'time' => $request->time,
                    ],
                ]);
            } elseif ($request->key == 'condition') {
                if ($request->type == 'open') {
                    return response()->json([
                        'status' => 'success',
                        'message' => trans('messages.automation.action.updated'),
                        'title' => trans('messages.automation.action.condition.read_email.title'),
                        'options' => [
                            'key' => $request->key,
                            'type' => $request->type,
                            'email' => empty($request->email) ? null : $request->email,
                            'wait' => $request->wait,
                        ],
                    ]);
                } elseif ($request->type == 'click') {
                    return response()->json([
                        'status' => 'success',
                        'message' => trans('messages.automation.action.updated'),
                        'title' => trans('messages.automation.action.condition.click_link.title'),
                        'options' => [
                            'key' => $request->key,
                            'type' => $request->type,
                            'email_link' => empty($request->email_link) ? null : $request->email_link,
                        ],
                    ]);
                } elseif ($request->type == 'cart_buy_anything') {
                    return response()->json([
                        'status' => 'success',
                        'message' => trans('messages.automation.action.updated'),
                        'title' => trans('messages.automation.action.condition.cart_buy_anything.title'),
                        'options' => [
                            'key' => $request->key,
                            'type' => $request->type,
                        ],
                    ]);
                } elseif ($request->type == 'cart_buy_item') {
                    return response()->json([
                        'status' => 'success',
                        'message' => trans('messages.automation.action.updated'),
                        'title' => trans('messages.automation.action.condition.cart_buy_item.title', [
                            'item' => $request->item_title,
                        ]),
                        'options' => [
                            'key' => $request->key,
                            'type' => $request->type,
                            'item_id' => $request->item_id,
                            'item_title' => $request->item_title,
                        ],
                    ]);
                }
                    // type is not receiving
                    else {
                        $finalData = [
                            'status' => 'success',
                            'message' => trans('messages.automation.action.updated'),
                            'title' => trans('messages.automation.action.condition.read_email.title'),
                            'options' => [
                                'key' => $request->key,
                                'type' => 'open',
                                // 'email' => '6481d0b601923',
                                'wait' => $request->wait,
                            ],
                        ];
                        return response()->json($finalData);
                    }
            } else {
                return response()->json([
                    'status' => 'success',
                    'message' => trans('messages.automation.action.updated'),
                    'title' => trans('messages.automation.action.title', [
                        'title' => trans('messages.automation.action.' . $request->key)
                    ]),
                    'options' => [
                        'key' => $request->key,
                        'after' => $request->after,
                    ],
                ]);
            }
        }
        
        return view('automation2.actionEdit', [
            'key' => $request->key,
            'automation' => $automation,
            'element' => $automation->getElement($request->id),
        ]);
    }
    
    /**
     * Email setup.
     *
     * @return \Illuminate\Http\Response
     */
    public function emailSetup(Request $request, $uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        
        if ($request->email_uid) {
            $email = Email::findByUid($request->email_uid);
        } else {
            $email = new Email([
                'sign_dkim' => true,
                'track_open' => true,
                'track_click' => true,
                'action_id' => $request->action_id,
            ]);
        }
        
        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }
        
        // saving
        if ($request->isMethod('post')) {
            // fill before save
            $email->fillAttributes($request->all());

            // Tacking domain
            if (isset($params['custom_tracking_domain']) && $params['custom_tracking_domain'] && isset($params['tracking_domain_uid'])) {
                $tracking_domain = \Acelle\Model\TrackingDomain::findByUid($params['tracking_domain_uid']);
                if (is_object($tracking_domain)) {
                    $this->tracking_domain_id = $tracking_domain->id;
                } else {
                    $this->tracking_domain_id = null;
                }
            } else {
                $this->tracking_domain_id = null;
            }
            
            // make validator
            $validator = Validator::make($request->all(), $email->rules($request));
            
            // redirect if fails
            if ($validator->fails()) {
                return response()->view('automation2.email.setup', [
                    'automation' => $automation,
                    'email' => $email,
                    'errors' => $validator->errors(),
                ], 400);
            }
            
            // pass validation and save
            $email->automation2_id = $automation->id;
            $email->customer_id = $automation->customer_id;
            $email->save();
            
            return response()->json([
                'status' => 'success',
                'title' => trans('messages.automation.send_a_email', ['title' => $email->subject]),
                'message' => trans('messages.automation.email.set_up.success'),
                'url' => action('Automation2Controller@emailTemplate', [
                    'uid' => $automation->uid,
                    'email_uid' => $email->uid,
                ]),
                'options' => [
                    'email_uid' => $email->uid,
                ],
            ], 201);
        }
        
        return view('automation2.email.setup', [
            'automation' => $automation,
            'email' => $email,
        ]);
    }

    /**
     * Delete automation email.
     *
     * @return \Illuminate\Http\Response
     */
    public function emailDelete(Request $request, $uid, $email_uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($email_uid);
        
        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }

        // delete email
        $email->deleteAndCleanup();

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.automation.email.deteled'),
        ], 201);
    }
    
    /**
     * Email template.
     *
     * @return \Illuminate\Http\Response
     */
    public function emailTemplate(Request $request, $uid, $email_uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($email_uid);
        
        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }
        
        if (!$email->hasTemplate()) {
            return redirect()->action('Automation2Controller@templateCreate', [
                'uid' => $automation->uid,
                'email_uid' => $email->uid,
            ]);
        }
        
        return view('automation2.email.template', [
            'automation' => $automation,
            'email' => $email,
        ]);
    }
    
    /**
     * Email show.
     *
     * @return \Illuminate\Http\Response
     */
    public function email(Request $request, $uid)
    {

        // init automation
        $automation = Automation2::findByUid($uid);
        $automations = AutomationList::findOrFail($automation->main_id);
        $email = Email::findByUid($request->email_uid);
        
        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }
        
        return view('automation2.email.index', [
            'automation' => $automations,
            'email' => $email,
        ]);
    }
    
    /**
     * Email confirm.
     *
     * @return \Illuminate\Http\Response
     */
    public function emailConfirm(Request $request, $uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);
        
        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }
        
        // saving
        if ($request->isMethod('post')) {
        }
        
        return view('automation2.email.confirm', [
            'automation' => $automation,
            'email' => $email,
        ]);
    }
    
    /**
     * Create template.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function templateCreate(Request $request, $uid, $email_uid)
    {
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);

        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }

        return view('automation2.email.template.create', [
            'automation' => $automation,
            'email' => $email,
        ]);
    }

    /**
     * Create template from layout.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function templateLayout(Request $request, $uid, $email_uid)
    {
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);

        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }

        if ($request->isMethod('post')) {
            $template = Template::findByUid($request->template_uid);
            $email->setTemplate($template);

            return response()->json([
                'status' => 'success',
                'message' => trans('messages.automation.email.template.theme.selected'),
            ], 201);
        }

        // GET goes here
        if ($request->category_uid) {
            $category = TemplateCategory::findByUid($request->category_uid);
        } else {
            $category = TemplateCategory::first();
        }

        return view('automation2.email.template.layout', [
            'automation' => $automation,
            'email' => $email,
            'category' => $category,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function templateLayoutList(Request $request)
    {
        $automation = Automation2::findByUid($request->uid);
        $email = Email::findByUid($request->email_uid);

        // from
        if ($request->from == 'mine') {
            $templates = $request->user()->customer->templates();
        } elseif ($request->from == 'gallery') {
            $templates = Template::shared();
        } else {
            $templates = Template::shared()
                ->orWhere('customer_id', '=', $request->user()->customer->id);
        }

        $templates = $templates->search($request->keyword)
            ->categoryUid($request->category_uid)
            ->orderBy($request->sort_order, $request->sort_direction)
            ->paginate($request->per_page);

        return view('automation2.email.template.layoutList', [
            'automation' => $automation,
            'email' => $email,
            'templates' => $templates,
        ]);
    }

    /**
     * Select builder for editing template.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function templateBuilderSelect(Request $request, $uid, $email_uid)
    {
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);

        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }

        return view('automation2.email.template.templateBuilderSelect', [
            'automation' => $automation,
            'email' => $email,
        ]);
    }

    /**
     * Edit campaign template.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function templateEdit(Request $request, $uid, $email_uid)
    {
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);

        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }

        // save campaign html
        if ($request->isMethod('post')) {
            $rules = array(
                'content' => 'required',
            );
            
            $this->validate($request, $rules);
            
            $email->setTemplateContent($request->content);
            $email->save();
            
            return response()->json([
                'status' => 'success',
            ]);
        }

        return view('automation2.email.template.edit', [
            'automation' => $automation,
            'list' => $automation->mailList,
            'email' => $email,
            'templates' => $request->user()->customer->getBuilderTemplates(),
        ]);
    }

    /**
     * Campaign html content.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function templateContent(Request $request, $uid, $email_uid)
    {
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);

        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }

        return view('automation2.email.template.content', [
            'content' => $email->getTemplateContent(),
        ]);
    }

    /**
     * Upload template.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function templateUpload(Request $request, $uid, $email_uid)
    {
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);
        
        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }

        // validate and save posted data
        if ($request->isMethod('post')) {
            $email->uploadTemplate($request);

            // return redirect()->action('CampaignController@template', $campaign->uid);
            return response()->json([
                'status' => 'success',
                'message' => trans('messages.automation.email.template.uploaded')
            ]);

            // throw a validation error otherwise
        }

        return view('automation2.email.template.upload', [
            'automation' => $automation,
            'email' => $email,
        ]);
    }
    
    /**
     * Remove exist template.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function templateRemove(Request $request, $uid, $email_uid)
    {
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);
        
        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }

        $email->removeTemplate();

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.automation.email.template.removed'),
        ], 201);
    }

    /**
     * Template preview.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function templatePreview(Request $request, $uid, $email_uid)
    {
        $automation = Automation2::findByUid($uid);
        $automations = AutomationList::findByUid($uid);
        $email = Email::findByUid($request->email_uid);
        
        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }

        return view('automation2.email.template.preview', [
            'automation' => $automations,
            'email' => $email,
        ]);
    }

    /**
     * Template preview.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function templatePreviewContent(Request $request, $uid, $email_uid)
    {
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);
        
        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }
        //dd($email->getHtmlContent());

        echo $email->getHtmlContent();
    }
    
    /**
     * Attachment upload.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function emailAttachmentUpload(Request $request, $uid, $email_uid)
    {
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);
        
        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }
        
        foreach ($request->file as $file) {
            $email->uploadAttachment($file);
        }
    }
    
    /**
     * Attachment remove.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function emailAttachmentRemove(Request $request, $uid, $email_uid, $attachment_uid)
    {
        $automation = Automation2::findByUid($uid);
        $attachment = Attachment::findByUid($request->attachment_uid);
        
        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }
        
        $attachment->remove();
        
        return response()->json([
            'status' => 'success',
            'message' => trans('messages.automation.email.attachment.removed'),
        ], 201);
    }
    
    /**
     * Attachment download.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function emailAttachmentDownload(Request $request, $uid, $email_uid, $attachment_uid)
    {
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);
        $attachment = Attachment::findByUid($request->attachment_uid);
        
        // authorize
        if (\Gate::denies('read', $automation)) {
            return $this->notAuthorized();
        }
        
        return response()->download(storage_path('app/' . $attachment->file), $attachment->name);
    }
    
    /**
     * Enable automation.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function enable(Request $request)
    {

        $automationLists = AutomationList::whereIn('uid',explode(',', $request->uids));
        foreach($automationLists->get() as $automationList){
            $automations = Automation2::where('main_id', $automationList->id);
            $automationList->status = 'active';
            $automationList->save();
            foreach ($automations->get() as $automation) {
                // authorize
                if (\Gate::allows('enable', $automation)) {
                    $automation->enable();
                }
            }
        }
        
        return response()->json([
            'status' => 'success',
            'message' => trans_choice('messages.automation.enabled', $automations->count()),
        ]);
    }
    
    /**
     * Disable event.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function disable(Request $request)
    {
        $automationLists = AutomationList::whereIn('uid',explode(',', $request->uids));
        foreach($automationLists->get() as $automationList){
            $automations = Automation2::where('main_id', $automationList->id);
            $automationList->status = 'inactive';
            $automationList->save();

            foreach ($automations->get() as $automation) {
                // authorize
                if (\Gate::allows('disable', $automation)) {
                    $automation->disable();
                }
            }
        }
        return response()->json([
            'status' => 'success',
            'message' => trans_choice('messages.automation.disabled', $automations->count()),
        ]);
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        if (isSiteDemo()) {
            return response()->json([
                'status' => 'notice',
                'message' => trans('messages.operation_not_allowed_in_demo'),
            ], 403);
        }
        
        $automationLists = AutomationList::whereIn('uid',explode(',', $request->uids));
        foreach($automationLists->get() as $automationList){
            $automations = Automation2::where('main_id', $automationList->id);
            $automationList->delete();
            foreach ($automations->get() as $automation) {
                // authorize
                if (\Gate::allows('delete', $automation)) {
                    $automation->delete();
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => trans_choice('messages.automation.deleted', $automations->count()),
        ]);
    }
    
    /**
     * Automation insight page.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function insight(Request $request, $uid)
    {
        $AutomationList = AutomationList::findByUid($uid);
        $automations = Automation2::where('main_id',$AutomationList->id)->get();
        
        // authorize
        // if (\Gate::denies('view', $automation)) {
        //     return $this->notAuthorized();
        // }
        $insight1 =array();
        $stats1 = array();
        
            //exit;
            //foreach
        foreach($automations as $automation){
            // $automation = Automation2::findByUid($automations1->uid);
            // var_dump($automations1);
            $stats1[] = $automation->readCache('SummaryStats');
            $insight1[] = $automation->getInsight();
        }
        //exit;
        return view('automation2.insight', [
            'automations' => $automations,
            'AutomationList' => $AutomationList,
            'stats1'=> $stats1,
            'insight1' => $insight1,
            
        ]);
    }
    
    /**
     * Automation contacts list.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function contacts(Request $request, $uid)
    {
     
        $automationList = AutomationList::findByUid($uid);
        //ump($automatiovar_dnList);
        if(!empty($automationList)){
            $automations = Automation2::where('main_id',$automationList->id);
        }else{
            $automations = Automation2::findByUid($uid);
        }
        
        // authorize
        // if (\Gate::denies('view', $automation)) {
        //     return $this->notAuthorized();
        // }
        foreach($automations->get() as $automation){
            $subscribers = $automation->subscribers();
            $count = $subscribers->count();
        }

      

        return view('automation2.contacts.index', [
            'automation' => $automation,
            'count' => $count,
            'stats' => $automation->getSummaryStats(),
            'subscribers' => $subscribers,
        ]);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function contactsList(Request $request, $uid)
    {
        $automation = Automation2::findByUid($uid);
        
        // authorize
        // if (\Gate::denies('view', $automation)) {
        //     return $this->notAuthorized();
        // }

        $sortBy = $request->sortBy ?: 'subscribers.id';
        $sortOrder = $request->sortOrder ?: 'DESC';

        // list by type
        $subscribers = $automation->getSubscribersWithTriggerInfo()
                                  ->simpleSearch($request->keyword)
                                  ->addSelect('subscribers.created_at')
                                  ->addSelect('auto_triggers.updated_at')
                                  ->orderBy($sortBy, $sortOrder);
        $contacts = $subscribers->paginate($request->per_page);
        
        return view('automation2.contacts.list', [
            'automation' => $automation,
            'contacts' => $contacts,
        ]);
    }
    
    /**
     * Automation timeline.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function timeline(Request $request, $uid)
    {
        $automation = Automation2::findByUid($uid);
        
        // authorize
        if (\Gate::denies('view', $automation)) {
            return $this->notAuthorized();
        }

        return view('automation2.timeline.index', [
            'automation' => $automation,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function timelineList(Request $request, $uid)
    {
        $automation = Automation2::findByUid($uid);
        
        // authorize
        if (\Gate::denies('view', $automation)) {
            return $this->notAuthorized();
        }
        
        $timelines = $automation->timelines()->paginate($request->per_page);
        
        return view('automation2.timeline.list', [
            'automation' => $automation,
            'timelines' => $timelines,
        ]);
    }
    
    /**
     * Automation contact profile.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function profile(Request $request, $uid, $contact_uid)
    {
        $automation = Automation2::findByUid($uid);
        $contact = Subscriber::findByUid($contact_uid);
        
        // authorize
        if (\Gate::denies('view', $automation)) {
            return $this->notAuthorized();
        }

        return view('automation2.profile', [
            'automation' => $automation,
            'contact' => $contact,
        ]);
    }

    /**
     * Automation remove contact.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function removeContact(Request $request, $uid, $contact_uid)
    {
        $automation = Automation2::findByUid($uid);
        $contact = Subscriber::findByUid($contact_uid);
        
        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.automation.contact.deleted'),
        ], 201);
    }

    /**
     * Automation tag contact.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function tagContact(Request $request, $uid, $contact_uid)
    {
        $automation = Automation2::findByUid($uid);
        $contact = Subscriber::findByUid($contact_uid);
        
        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }

        // saving
        if ($request->isMethod('post')) {
            $contact->updateTags($request->tags);

            return response()->json([
                'status' => 'success',
                'message' => trans('messages.automation.contact.tagged', [
                    'contact' => $contact->getFullName(),
                ]),
            ], 201);
        }

        return view('automation2.contacts.tagContact', [
            'automation' => $automation,
            'contact' => $contact,
        ]);
    }

    /**
     * Automation tag contacts.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function tagContacts(Request $request, $uid)
    {
        $automation = Automation2::findByUid($uid);
        $subscribers = $automation->subscribers();
        
        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }

        // saving
        if ($request->isMethod('post')) {
            // make validator
            $validator = Validator::make($request->all(), [
                'tags' => 'required',
            ]);

            // redirect if fails
            if ($validator->fails()) {
                return response()->view('automation2.contacts.tagContacts', [
                    'automation' => $automation,
                    'subscribers' => $subscribers,
                    'errors' => $validator->errors(),
                ], 400);
            }

            // Copy to list
            foreach ($subscribers->get() as $subscriber) {
                $subscriber->addTags($request->tags);
            }

            return response()->json([
                'status' => 'success',
                'message' => trans('messages.automation.contacts.tagged', [
                    'count' => $subscribers->count(),
                ]),
            ], 201);
        }

        return view('automation2.contacts.tagContacts', [
            'automation' => $automation,
            'subscribers' => $subscribers,
        ]);
    }

    /**
     * Automation remove contact tag.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function removeTag(Request $request, $uid, $contact_uid)
    {
        $automation = Automation2::findByUid($uid);
        $contact = Subscriber::findByUid($contact_uid);
        
        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }

        $contact->removeTag($request->tag);

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.automation.contact.tag.removed', [
                'tag' => $request->tag,
            ]),
        ], 201);
    }
    
    /**
     * Automation export contacts.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function exportContacts(Request $request, $uid)
    {
        $automation = Automation2::findByUid($uid);
        
        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }

        $subscribers = $automation->subscribers();

        // saving
        if ($request->isMethod('post')) {
            return response()->json([
                'status' => 'success',
                'message' => trans('messages.automation.contacts.exported'),
            ], 201);
        }
    }

    /**
     * Automation copy contacts to new list.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function copyToNewList(Request $request, $uid)
    {
        $automation = Automation2::findByUid($uid);
        
        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }

        $subscribers = $subscribers = $automation->subscribers();

        // saving
        if ($request->isMethod('post')) {
            // make validator
            $validator = Validator::make($request->all(), [
                'name' => 'required',
            ]);

            // redirect if fails
            if ($validator->fails()) {
                return response()->view('automation2.contacts.copyToNewList', [
                    'automation' => $automation,
                    'subscribers' => $subscribers,
                    'errors' => $validator->errors(),
                ], 400);
            }

            // Crate new list
            $list = $automation->mailList->copy($request->name);

            // Copy to list
            foreach ($subscribers->get() as $subscriber) {
                $subscriber->copy($list);
            }

            // update cache
            $list->updateCache();

            return response()->json([
                'status' => 'success',
                'message' => trans('messages.automation.contacts.copied_to_new_list', [
                    'count' => $subscribers->count(),
                    'list' => $list->name,
                ]),
            ], 201);
        }

        return view('automation2.contacts.copyToNewList', [
            'automation' => $automation,
            'subscribers' => $subscribers,
        ]);
    }

    /**
     * Automation template classic builder.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function templateEditClassic(Request $request, $uid, $email_uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($email_uid);
        
        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }

        // saving
        if ($request->isMethod('post')) {
            $rules = array(
                'content' => 'required',
            );
            
            $this->validate($request, $rules);

            $email->setTemplateContent($request->content);

            return response()->json([
                'status' => 'success',
                'message' => trans('messages.automation.email.content.updated'),
            ], 201);
        }

        return view('automation2.email.template.editClassic', [
            'automation' => $automation,
            'email' => $email,
        ]);
    }

    /**
     * Automation template classic builder.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function templateEditPlain(Request $request, $uid, $email_uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($email_uid);
        
        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }

        // saving
        if ($request->isMethod('post')) {
            $rules = array(
                'plain' => 'required',
            );
            
            // make validator
            $validator = Validator::make($request->all(), $rules);
            
            // redirect if fails
            if ($validator->fails()) {
                return response()->view('automation2.email.template.editPlain', [
                    'automation' => $automation,
                    'email' => $email,
                    'errors' => $validator->errors(),
                ], 400);
            }
            
            $email->plain = $request->plain;
            $email->save();

            return response()->json([
                'status' => 'success',
                'message' => trans('messages.automation.email.plain.updated'),
            ], 201);
        }

        return view('automation2.email.template.editPlain', [
            'automation' => $automation,
            'email' => $email,
        ]);
    }

    /**
     * Segment select.
     *
     * @return \Illuminate\Http\Response
     */
    public function segmentSelect(Request $request)
    {
        if (!$request->list_uid) {
            return '';
        }

        // init automation
        if ($request->uid) {
            $automation = AutomationList::findByUid($request->uid);

            // authorize
            // if (\Gate::denies('view', $automation)) {
            //     return $this->notAuthorized();
            // }
        } else {
            $automation = new AutomationList();

            // authorize
            // if (\Gate::denies('create', $automation)) {
            //     return $this->notAuthorized();
            // }
        }
        $list = MailList::findByUid($request->list_uid);
        
        return view('automation2.segmentSelect', [
            'automation' => $automation,
            'list' => $list,
        ]);
    }

    /**
     * Display a listing of subscribers.
     *
     * @return \Illuminate\Http\Response
     */
    public function subscribers(Request $request, $uid)
    {
        // init
        
        $automation = AutomationList::findByUid($uid);
        $list = $automation->mailList;

        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }

        return view('automation2.subscribers.index', [
            'automation' => $automation,
            'list' => $list,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function subscribersList(Request $request, $uid)
    {
        // init
        $automation = AutomationList::findByUid($uid);
        $list = $automation->mailList;

        // authorize
        // if (\Gate::denies('update', $automation)) {
        //     return $this->notAuthorized();
        // }

        $subscribers = $automation->subscribers()->search($request)
            ->where('mail_list_id', '=', $list->id);

        // $total = distinctCount($subscribers);
        $total = $subscribers->count();
        $subscribers->with(['mailList', 'subscriberFields']);
        $subscribers = \optimized_paginate($subscribers, $request->per_page, null, null, null, $total);

        $fields = $list->getFields->whereIn('uid', explode(',', $request->columns));

        return view('automation2.subscribers._list', [
            'automation' => $automation,
            'subscribers' => $subscribers,
            'total' => $total,
            'list' => $list,
            'fields' => $fields,
        ]);
    }

    /**
     * Remove subscriber from automation.
     *
     * @return \Illuminate\Http\Response
     */
    public function subscribersRemove(Request $request, $uid, $subscriber_uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        $subscriber = Subscriber::findByUid($subscriber_uid);

        // authorize
        if (\Gate::denies('update', $subscriber)) {
            return;
        }

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.automation.subscriber.removed'),
        ], 201);
    }

    /**
     * Restart subscriber for automation.
     *
     * @return \Illuminate\Http\Response
     */
    public function subscribersRestart(Request $request, $uid, $subscriber_uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        $subscriber = Subscriber::findByUid($subscriber_uid);

        // authorize
        if (\Gate::denies('update', $subscriber)) {
            return;
        }

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.automation.subscriber.restarted'),
        ], 201);
    }

    /**
     * Display a listing of subscribers.
     *
     * @return \Illuminate\Http\Response
     */
    public function subscribersShow(Request $request, $uid, $subscriber_uid)
    {
        // init automation

        $automation = Automation2::findByUid($uid);
        $subscriber = Subscriber::findByUid($subscriber_uid);
        // authorize
        if (\Gate::denies('read', $subscriber)) {
            return;
        }

        return view('automation2.subscribers.show', [
            'automation' => $automation,
            'subscriber' => $subscriber,
        ]);
    }
    
    /**
     * Get last saved time.
     *
     * @return \Illuminate\Http\Response
     */
    public function lastSaved(Request $request, $uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);

        // authorize
        if (\Gate::denies('view', $automation)) {
            return;
        }

        return trans('messages.automation.designer.last_saved', ['time' => $automation->updated_at->diffForHumans()]);
    }

    public function debug(Request $request, $uid)
    {
        $automation = Automation2::findByUid($uid);
        $type = $automation->getTriggerAction()->getOption('key');

        switch ($type) {
            case Automation2::TRIGGER_TYPE_WELCOME_NEW_SUBSCRIBER:
                
                $subscribers = $automation->getSubscribersWithTriggerInfo();

                if ($request->input('orderBy')) {
                    $subscribers = $subscribers->orderBy($request->input('orderBy'), $request->input('orderDir'));
                }

                $subscribers = $subscribers->simplePaginate(50);

                return view('automation2.debug_list_subscription', [
                    'automation' => $automation,
                    'subscribers' => $subscribers,
                ]);

                break;

            case Automation2::TRIGGER_TYPE_SAY_GOODBYE_TO_SUBSCRIBER:

                $subscribers = $automation->getSubscribersWithTriggerInfo()->where('subscribers.status', '=', 'unsubscribed');

                if ($request->input('orderBy')) {
                    $subscribers = $subscribers->orderBy($request->input('orderBy'), $request->input('orderDir'));
                }

                $subscribers = $subscribers->simplePaginate(50);

                return view('automation2.debug_list_unsubscription', [
                    'automation' => $automation,
                    'subscribers' => $subscribers,
                ]);

                break;

            case Automation2::TRIGGER_TYPE_SAY_HAPPY_BIRTHDAY:
                $subscribers = $automation->getSubscribersWithDateOfBirth();

                if ($request->input('email')) {
                    $subscribers = $subscribers->searchByEmail($request->input('email'));
                }

                if ($request->input('orderBy')) {
                    $subscribers = $subscribers->orderBy($request->input('orderBy'), $request->input('orderDir'));
                }

                $subscribers = $subscribers->simplePaginate(50);

                return view('automation2.debug', [
                    'automation' => $automation,
                    'subscribers' => $subscribers,
                ]);

                break;

            default:
                # code...
                break;
        }
    }

    public function debugTrigger(Request $request, $uid)
    {
        $trigger = AutoTrigger::findByUid($uid);

        return view('automation2.debug', [
            'trigger' => $trigger,
        ]);
    }

    public function triggerNow(Request $request)
    {
        $automation = Automation2::findByUid($request->automation);
        $subscriber = Subscriber::findByUid($request->subscriber);

        $existingTrigger = $automation->getAutoTriggerFor($subscriber);

        if (!is_null($existingTrigger)) {
            echo sprintf("%s already triggered. Click <a href='%s'>here</a> for more details", $subscriber->email, action('AutoTrigger@show', [ 'id' => $existingTrigger->id ]));
            return;
        }

        // Manually trigger, force!
        $automation->logger()->info(sprintf('Manually trigger contact %s', $subscriber->email));
        
        // Force trigger a contact
        // Even inactive contacts - in case of Say-Goodbye-Trigger for example
        $trigger = $automation->initTrigger($subscriber, $force = true);

        return redirect()->action('AutoTrigger@show', [ 'id' => $trigger->id ]);
    }

    /**
     * Get last saved time.
     *
     * @return \Illuminate\Http\Response
     */
    public function contactRetry(Request $request, $uid, $contact_uid)
    {
        $automation = AutomationList::findByUid($uid);
        $contact = Subscriber::findByUid($contact_uid);
        // authorize
        // if (\Gate::denies('view', $automation)) {
        //     return $this->notAuthorized();
        // }

        return view('automation2.contacts.list.error_row', [
            'automation' => $automation,
            'contact' => $contact,
        ]);
    }

    /**
     * Get wait time.
     *
     * @return \Illuminate\Http\Response
     */
    public function waitTime(Request $request, $uid)
    {
        $automation = Automation2::findByUid($uid);

        // saving
        if ($request->isMethod('post')) {
            return response()->json([
                'status' => 'success',
                'amount' => $request->amount,
                'unit' => $request->unit
            ]);
        }

        return view('automation2.waitTime', [
            'automation' => $automation,
        ]);
    }

    /**
     * Change cart automation2 list for auto adding byuer.
     *
     * @return \Illuminate\Http\Response
     */
    public function cartWait(Request $request, $uid)
    {
        $automation = Automation2::findByUid($uid);

        // saving
        if ($request->isMethod('post')) {
            // make validator
            $validator = Validator::make($request->all(), [
                'amount' => 'required',
                'unit' => 'required',
            ]);
            // redirect if fails
            if ($validator->fails()) {
                return response()->view('automation2.cartWait', [
                    'automation' => $automation,
                    'trigger' => $automation->getTrigger(),
                    'errors' => $validator->errors(),
                ], 400);
            }

            return response()->json([
                'status' => 'success',
                'message' => trans('messages.cart.wait_updated'),
                'options' => [
                    'wait' => $request->amount . "_" . $request->unit,
                ]
            ]);
        }

        return view('automation2.cartWait', [
            'automation' => $automation,
            'trigger' => $automation->getTrigger(),
        ]);
    }

    /**
     * Change cart automation2 list for auto adding byuer.
     *
     * @return \Illuminate\Http\Response
     */
    public function cartChangeList(Request $request, $uid)
    {
        $automation = Automation2::findByUid($uid);

        // saving
        if ($request->isMethod('post')) {
            // make validator
            $validator = Validator::make($request->all(), [
                'options.list_uid' => 'required',
            ]);
            // redirect if fails
            if ($validator->fails()) {
                return response()->view('automation2.cartChangeList', [
                    'automation' => $automation,
                    'trigger' => $automation->getTrigger(),
                    'errors' => $validator->errors(),
                ], 400);
            }

            return response()->json([
                'status' => 'success',
                'message' => trans('messages.cart.list_updated'),
                'options' => $request->options
            ]);
        }

        return view('automation2.cartChangeList', [
            'automation' => $automation,
            'trigger' => $automation->getTrigger(),
        ]);
    }
    
    /**
     * Change cart automation2 list for auto adding byuer.
     *
     * @return \Illuminate\Http\Response
     */
    public function cartChangeStore(Request $request, $uid)
    {
        $automation = Automation2::findByUid($uid);

        // saving
        if ($request->isMethod('post')) {
            // make validator
            $validator = Validator::make($request->all(), [
                'options.source_uid' => 'required',
            ]);
            // redirect if fails
            if ($validator->fails()) {
                return response()->view('automation2.cartChangeSore', [
                    'automation' => $automation,
                    'trigger' => $automation->getTrigger(),
                    'errors' => $validator->errors(),
                ], 400);
            }

            return response()->json([
                'status' => 'success',
                'message' => trans('messages.cart.store_updated'),
                'options' => $request->options
            ]);
        }

        return view('automation2.cartChangeSore', [
            'automation' => $automation,
            'trigger' => $automation->getTrigger(),
        ]);
    }

    /**
     * Cart stats.
     *
     * @return \Illuminate\Http\Response
     */
    public function cartStats(Request $request, $uid)
    {
        $automation = Automation2::findByUid($uid);

        return view('automation2.cart.stats', [
            'automation' => $automation,
        ]);
    }

    /**
     * Cart list.
     *
     * @return \Illuminate\Http\Response
     */
    public function cartList(Request $request, $uid)
    {
        $automation = Automation2::findByUid($uid);

        return view('automation2.cart.list', [
            'automation' => $automation,
        ]);
    }

    /**
     * Cart list.
     *
     * @return \Illuminate\Http\Response
     */
    public function cartItems(Request $request, $uid)
    {
        $automation = Automation2::findByUid($uid);

        return view('automation2.cart.items', [
            'automation' => $automation,
        ]);
    }

    /**
     * Operation select popup.
     *
     * @return \Illuminate\Http\Response
     */
    public function operationSelect(Request $request, $uid)
    {
        $automation = Automation2::findByUid($uid);

        // saving
        if ($request->isMethod('post')) {
            // return response()->json([
            //     'status' => 'success',
            //     'amount' => $request->amount,
            //     'unit' => $request->unit
            // ]);
        }

        return view('automation2.operationSelect', [
            'automation' => $automation,
            'types' => [
                'update_contact',
                'tag_contact',
                'copy_contact',
            ],
        ]);
    }

    /**
     * Operation edit popup.
     *
     * @return \Illuminate\Http\Response
     */
    public function operationCreate(Request $request, $uid)
    {
        $automation = Automation2::findByUid($uid);

        // saving
        if ($request->isMethod('post')) {
            return response()->json([
                'status' => 'success',
                'title' => trans('messages.automation.operation.' . $request->options['operation_type']),
                'options' => $request->options,
                'message' => trans('messages.automation.operation.added'),
            ]);
        }

        return view('automation2.operationCreate', [
            'automation' => $automation,
            'types' => [
                'update_contact',
                'tag_contact',
                'copy_contact',
            ],
        ]);
    }

    /**
     * Operation edit popup.
     *
     * @return \Illuminate\Http\Response
     */
    public function operationShow(Request $request, $uid)
    {
        $automation = Automation2::findByUid($uid);

        return view('automation2.operationShow', [
            'automation' => $automation,
            'element' => $automation->getElement($request->id),
        ]);
    }

    /**
     * Operation edit popup.
     *
     * @return \Illuminate\Http\Response
     */
    public function operationEdit(Request $request, $uid)
    {
        $automation = Automation2::findByUid($uid);

        // saving
        if ($request->isMethod('post')) {
            return response()->json([
                'status' => 'success',
                'title' => trans('messages.automation.operation.' . $request->options['operation_type']),
                'options' => $request->options,
                'message' => trans('messages.automation.operation.updated'),
            ]);
        }

        return view('automation2.operationEdit', [
            'automation' => $automation,
            'element' => $automation->getElement($request->id),
        ]);
    }

    public function run(Request $request)
    {
        $automation = Automation2::findByUid($request->automation);
        $automation->check();
        echo "Done";
    }

    public function sendTestEmail(Request $request)
    {
        $email = Email::findByUid($request->email_uid);

        if ($request->isMethod('post')) {
            $validator = \Validator::make($request->all(), [
                'email' => 'required|email',
            ]);

            // 
            if ($validator->fails()) {
                return response()->view('automation2.sendTestEmail', [
                    'email' => $email,
                    'errors' => $validator->errors(),
                ], 400);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'OK',
            ]);
        }

        return view('automation2.sendTestEmail', [
            'email' => $email,
        ]);
    }

    public function conditionWaitCustom(Request $request)
    {
        if ($request->isMethod('post')) {
            // make validator
            $validator = \Validator::make($request->all(), [
                'wait_amount' => 'required',
                'wait_unit' => 'required',
            ]);

            // redirect if fails
            if ($validator->fails()) {
                return response()->view('automation2.condition.conditionWaitCustom', [
                    'errors' => $validator->errors(),
                ], 400);
            }

            return view('automation2.condition._wait_select');
        }

        return view('automation2.condition.conditionWaitCustom');
    }

    public function stats($uid){
        $AutomationList = AutomationList::findByUid($uid);
        $automations = Automation2::where('main_id',$AutomationList->id)->get();
        $automation = Automation2::where('main_id',$AutomationList->id)->first();
        //dd($automations,$automation);
        $diffAutomation = '';
        $segmentName = 'B';
        $data = array();
        foreach($automations as $test){
            $trigerId[] = $test->clickLinksTrigger(); 
            if($automation->id != $test->id){
                $diffAutomation = $test->uid;
            }
            
        }
        foreach($trigerId as $ids){
            foreach($ids as $id){
                $data[] = $id;
            }
            
        }
        $topClick = Automation2::clickLinks($data);
        // $topOpens = Automation2::openLinks($data);
        // $topClickemail = Automation2::topClickEmail($data);
        // dd($topOpens,$topClickemail);
        // dd($topClickemail);

        return view('automation2.stats',[
            'AutomationList' => $AutomationList,
                'automations' => $automations,
                'automation' => $automation,
                'topClick' => $topClick,
                'diffAutomation' => $diffAutomation,
                'segmentName' => $segmentName,
                'uid' => $uid
        ]);
    }

    public function statsLog($uid,$page,$state){
            $page_array = [];
            $limit = 5;

               // $page = 1;

                if($page > 1)
                {
                    $start = (($page - 1) * $limit);

                    $page = $page;
                }
                else
                {
                    $start = 0;
                } 



        $AutomationList = AutomationList::findByUid($uid);
        $automations = Automation2::where('main_id',$AutomationList->id)->get();
        $automation = Automation2::where('main_id',$AutomationList->id)->first();
        //dd($automations,$automation);
        $diffAutomation = '';
        $segmentName = 'B';
        $data = array();
        foreach($automations as $test){
            $trigerId[] = $test->clickLinksTrigger(); 
            if($automation->id != $test->id){
                $diffAutomation = $test->uid;
            }
            
        }
        foreach($trigerId as $ids){
            foreach($ids as $id){
                $data[] = $id;
            }
            
        }
        $total_data = 0;
        $pagination_html = '';
       // $topClick = Automation2::clickLinks($data);
        if($state == 'Click'){
               $result = Automation2::topClickEmail($data,$start,$limit);
               $total_data = Automation2::topClickEmailCount($data) ? Automation2::topClickEmailCount($data)->count() : 0;

          }else{
            $result = Automation2::openLinks($data,$start,$limit);
            $total_data = Automation2::openLinksCount($data) ? Automation2::openLinksCount($data)->count() : 0;
          }
        //$total_data  = $result->count();
        if($total_data >0){
        $pagination_html = '
    <div align="center">
        <ul class="pagination">
    ';

    $total_links = ceil($total_data/$limit);

    $previous_link = '';

    $next_link = '';

    $page_link = '';

    if($total_links > 4)
    {
        if($page < 5)
        {
            for($count = 1; $count <= 5; $count++)
            {
                $page_array[] = $count;
            }
            $page_array[] = '...';
            $page_array[] = $total_links;
        }
        else
        {
            $end_limit = $total_links - 5;

            if($page > $end_limit)
            {
                $page_array[] = 1;

                $page_array[] = '...';

                for($count = $end_limit; $count <= $total_links; $count++)
                {
                    $page_array[] = $count;
                }
            }
            else
            {
                $page_array[] = 1;

                $page_array[] = '...';

                for($count = $page - 1; $count <= $page + 1; $count++)
                {
                    $page_array[] = $count;
                }

                $page_array[] = '...';

                $page_array[] = $total_links;
            }
        }
    }
    else
    {
        for($count = 1; $count <= $total_links; $count++)
        {
            $page_array[] = $count;
        }
    }

    for($count = 0; $count < count($page_array); $count++)
    {
        if($page == $page_array[$count])
        {
            $page_link .= '
            <li class="page-item active">
                <a class="page-link" href="#">'.$page_array[$count].' <span class="sr-only">(current)</span></a>
            </li>
            ';

            $previous_id = $page_array[$count] - 1;

            if($previous_id > 0)
            {
                $previous_link = '<li class="page-item"><a class="page-link" href="javascript:swapbutton(`'.$state.'`, '.$previous_id.')">Previous</a></li>';
            }
            else
            {
                $previous_link = '
                <li class="page-item disabled">
                    <a class="page-link" href="#">Previous</a>
                </li>
                ';
            }

            $next_id = $page_array[$count] + 1;

            if($next_id >= $total_links)
            {
                $next_link = '
                <li class="page-item disabled">
                    <a class="page-link" href="#">Next</a>
                </li>
                ';
            }
            else
            {
                $next_link = '
                <li class="page-item"><a class="page-link" href="javascript:swapbutton(`'.$state.'`, '.$next_id.')">Next</a></li>
                ';
            }

        }
        else
        {
            if($page_array[$count] == '...')
            {
                $page_link .= '
                <li class="page-item disabled">
                    <a class="page-link" href="#">...</a>
                </li>
                ';
            }
            else
            {
                $page_link .= '
                <li class="page-item">
                    <a class="page-link" href="javascript:swapbutton(`'.$state.'`, '.$page_array[$count].')">'.$page_array[$count].'</a>
                </li>
                ';
            }
        }
    }

    $pagination_html .= $previous_link . $page_link . $next_link;


    $pagination_html .= '
        </ul>
    </div>
    ';
}
       // dd($total_data,$pagination_html);
            $output = array(
        'data'              =>  $result,
        'pagination'        =>  $pagination_html,
        'total_data'        =>  $total_data
    );

    echo json_encode($output);
    //  return array();
        // dd($topOpens,$topClickemail);
        // dd($topClickemail);

        // return view('automation2.stats',[
        //     'AutomationList' => $AutomationList,
        //         'automations' => $automations,
        //         'automation' => $automation,
        //         'topClick' => $topClick,
        //         'openClicks' => $topOpens,
        //         'topClickemail' => $topClickemail,
        //         'diffAutomation' => $diffAutomation,
        //         'segmentName' => $segmentName
        // ]);
    }
}
