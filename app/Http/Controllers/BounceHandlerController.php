<?php

namespace Acelle\Http\Controllers;

use Illuminate\Http\Request;
use Acelle\Http\Controllers\Controller;
use DB;
use Auth;

class BounceHandlerController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // if (\Gate::denies('read', new \Acelle\Model\BounceHandler())) {
        //     return $this->notAuthorized();
        // }

        // // If admin can view all sending domains
        // if (!$request->user()->admin->can("readAll", new \Acelle\Model\BounceHandler())) {
        //     $request->merge(array("admin_id" => $request->user()->admin->id));
        // }

        $items = \Acelle\Model\BounceHandler::search($request);

        return view('bounce_handlers.index', [
            'items' => $items,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function listing(Request $request)
    {
        // if (\Gate::denies('read', new \Acelle\Model\BounceHandler())) {
        //     return $this->notAuthorized();
        // }

        // // If admin can view all sending domains
        // if (!$request->user()->admin->can("readAll", new \Acelle\Model\BounceHandler())) {
        //     $request->merge(array("admin_id" => $request->user()->admin->id));
        // }

        $items = \Acelle\Model\BounceHandler::search($request)->paginate($request->per_page);

        return view('bounce_handlers._list', [
            'items' => $items,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $server = new \Acelle\Model\BounceHandler();
        $server->status = 'active';
        $server->uid = '0';
        $server->fill($request->old());

        // authorize
        // if (\Gate::denies('create', $server)) {
        //     return $this->notAuthorized();
        // }

        return view('bounce_handlers.create', [
            'server' => $server,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Get current user
        $current_user = $request->user();
        $server = new \Acelle\Model\BounceHandler();

        // authorize
        // if (\Gate::denies('create', $server)) {
        //     return $this->notAuthorized();
        // }

        // save posted data
        if ($request->isMethod('post')) {
            $this->validate($request, \Acelle\Model\BounceHandler::rules());

            // Save current user info
            $server->fill($request->all());
            $server->admin_id = $request->user()->customer->admin_id;
            $server->status = 'active';

            if ($server->save()) {
                $request->session()->flash('alert-success', trans('messages.bounce_handler.created'));


                return redirect()->action('BounceHandlerController@index');
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $server = \Acelle\Model\BounceHandler::findByUid($id);

        // authorize
        // if (\Gate::denies('update', $server)) {
        //     return $this->notAuthorized();
        // }

        $server->fill($request->old());

        return view('bounce_handlers.edit', [
            'server' => $server,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Get current user
        $current_user = $request->user();
        $server = \Acelle\Model\BounceHandler::findByUid($id);

        // authorize
        // if (\Gate::denies('update', $server)) {
        //     return $this->notAuthorized();
        // }

        // save posted data
        if ($request->isMethod('patch')) {
            $this->validate($request, \Acelle\Model\BounceHandler::rules());

            // Save current user info
            $server->fill($request->all());

            if ($server->save()) {
                $request->session()->flash('alert-success', trans('messages.bounce_handler.updated'));

                return redirect()->action('BounceHandlerController@index');
            }
        }
    }

    /**
     * Custom sort items.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function sort(Request $request)
    {
        echo trans('messages._deleted_');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $items = \Acelle\Model\BounceHandler::whereIn('uid', explode(',', $request->uids));

        foreach ($items->get() as $item) {
            // authorize
            // if (\Gate::denies('delete', $item)) {
            //     return;
            // }
        }

        foreach ($items->get() as $item) {
            $item->delete();
        }

        // Redirect to my lists page
        echo trans('messages.bounce_handlers.deleted');
    }

    /**
     * Test Bounce handler.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function test(Request $request, $uid)
    {
        // Get current user
        $current_user = $request->user();

        // Fill new server info
        if ($uid) {
            $server = \Acelle\Model\BounceHandler::findByUid($uid);
        } else {
            $server = new \Acelle\Model\BounceHandler();
        }

        $server->fill($request->all());

        // authorize
        // if (\Gate::denies('test', $server)) {
        //     return $this->notAuthorized();
        // }

        try {
            $server->test();
            echo json_encode([
                'status' => 'success', // or success
                'message' => trans('messages.bounce_handler.test_success')
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'status' => 'error', // or success
                'message' => $e->getMessage()
            ]);
        }
    }

    public function updateServer($uid,$bounceId){
        $server = \Acelle\Model\SendingServer::findByUid($uid);
        $server->bounce_handler_id = $bounceId;
        $server->save();
        $sql = "update bounce_handeler_notification set status = 1 where user_id = ".Auth::user()->id;
        
        DB::statement($sql);
        return true;
    }
}
