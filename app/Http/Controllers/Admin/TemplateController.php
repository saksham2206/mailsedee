<?php

namespace Acelle\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Acelle\Http\Controllers\Controller;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use Acelle\Model\Template;
use Acelle\Library\Rss;
use Acelle\Model\Setting;
use Acelle\Model\Automation2;
use App;

class TemplateController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {

    }
    public function index(Request $request)
    {
        

        return view('admin.templates.index');
    }

    public function searchForId($search_value, $array, $id_path) {
  
    // Iterating over main array
    foreach ($array as $key1 => $val1) {
        //var_dump($array);
        //dd($val1);
        // $temp_path = $id_path;
          
        // // Adding current key to search path
        // array_push($temp_path, $key1);
  
        // Check if this value is an array
        // with atleast one element
        
            if(is_array($val1) and count($val1)) {
  
                // Iterating over the nested array
                foreach ($val1 as $key2 => $val2) {
                    if($val1['id'] ==  $search_value){
                        return $val1;
                    }
                    //dd($val1);
                    // if($key1 == 1){
                    //     dd($search_value,$val2);
                    // }
                    // if($val2 == $search_value) {
                              
                    //     // Adding current key to search path
                    //     return $val1;
                    // }else{
                    //     return null;
                    // }
                }
            }
        
        
          
        // elseif($val1 == $search_value) {
        //     return $val1;
        // }
    }
      
    return null;
}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function listing(Request $request)
    {
        if (!$request->user()->admin->can('read', new \Acelle\Model\Template())) {
            return $this->notAuthorized();
        }

        $templates = Template::shared()
            ->categoryUid($request->category_uid)
            ->search($request->keyword)
            ->orderBy($request->sort_order, $request->sort_direction)
            ->paginate($request->per_page);

        return view('admin.templates._list', [
            'templates' => $templates,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        // Generate info
        $user = $request->user();
        $template = new \Acelle\Model\Template();

        // authorize
        if (!$request->user()->admin->can('create', Template::class)) {
            return $this->notAuthorized();
        }

        // Get old post values
        if (null !== $request->old()) {
            $template->fill($request->old());
        }

        return view('admin.templates.create', [
            'template' => $template,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $uid)
    {
        // Generate info
        $user = $request->user();
        $template = Template::findByUid($uid);

        // authorize
        if (!$request->user()->admin->can('update', $template)) {
            return $this->notAuthorized();
        }

        // Get old post values
        if (null !== $request->old()) {
            $template->fill($request->old());
        }

        return view('admin.templates.edit', [
            'template' => $template,
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
        // Generate info
        $user = $request->user();
        $template = Template::findByUid($request->uid);

        // authorize
        if (!$request->user()->admin->can('update', $template)) {
            return $this->notAuthorized();
        }

        // validate and save posted data
        if ($request->isMethod('patch')) {
            // Save template
            $template->fill($request->all());

            $rules = array(
                'name' => 'required',
                'content' => 'required',
            );

            // make validator
            $validator = \Validator::make($request->all(), $rules);
            
            // redirect if fails
            if ($validator->fails()) {
                // faled
                return response()->json($validator->errors(), 400);
            }
            
            $template->save();

            // success
            return response()->json([
                'status' => 'success',
                'message' => trans('messages.template.updated'),
            ], 201);
        }
    }

    /**
     * Upload template.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function uploadTemplate(Request $request)
    {
        // authorize
        if (!$request->user()->admin->can('create', Template::class)) {
            return $this->notAuthorized();
        }

        // validate and save posted data
        if ($request->isMethod('post')) {
            $asAdmin = true;
            $template = Template::uploadSystemTemplate($request, $asAdmin);

            if (!empty(Setting::get('storage.s3'))) {
                App::make('xstore')->store($template);
            }

            $request->session()->flash('alert-success', trans('messages.template.uploaded'));
            return redirect()->action('Admin\TemplateController@index');
        }

        return view('admin.templates.upload');
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

        $templates = Template::whereIn('uid', explode(',', $request->uids));
        $total = $templates->count();
        $deleted = 0;
        foreach ($templates->get() as $template) {
            // authorize
            if ($request->user()->admin->can('delete', $template)) {
                $template->deleteAndCleanup();
                $deleted += 1;
            }
        }

        echo trans('messages.templates.deleted', [ 'deleted' => $deleted, 'total' => $total]);
    }

    /**
     * Preview template.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function preview(Request $request, $id)
    {
        $template = Template::findByUid($id);

        // authorize
        if (!$request->user()->admin->can('preview', $template)) {
            return $this->notAuthorized();
        }

        return view('admin.templates.preview', [
            'template' => $template,
        ]);
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
     * Copy template.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function copy(Request $request)
    {
        $template = Template::findByUid($request->uid);

        if ($request->isMethod('post')) {
            // authorize
            if (!$request->user()->admin->can('copy', $template)) {
                return $this->notAuthorized();
            }

            $template->copy($request->name, null, $request->user()->admin);

            echo trans('messages.template.copied');
            return;
        }

        return view('admin.templates.copy', [
            'template' => $template,
        ]);
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function builderEdit(Request $request, $uid)
    {
        // Generate info
        $user = $request->user();
        $template = Template::findByUid($uid);

        // authorize
        if (!$request->user()->admin->can('update', $template)) {
            return $this->notAuthorized();
        }
        
        // validate and save posted data
        if ($request->isMethod('post')) {
            $rules = array(
                'content' => 'required',
            );

            $this->validate($request, $rules);
            
            $template->content = $request->content;
            $template->save();

            return response()->json([
                'status' => 'success',
            ]);
        }

        return view('admin.templates.builder.edit', [
            'template' => $template,
            'templates' => $template->getBuilderAdminTemplates(),
        ]);
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function builderEditContent(Request $request, $uid)
    {
        // Generate info
        $user = $request->user();
        $template = Template::findByUid($uid);
        
        // authorize
        if (!$request->user()->admin->can('update', $template)) {
            return $this->notAuthorized();
        }

        return view('admin.templates.builder.content', [
            'content' => $template->content,
        ]);
    }
    
    /**
     * Upload asset to builder.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function uploadTemplateAssets(Request $request, $uid)
    {
        $template = Template::findByUid($uid);
        
        // authorize
        if (!$request->user()->admin->can('update', $template)) {
            return $this->notAuthorized();
        }
        
        if ($request->assetType == 'upload') {
            $assetUrl = $template->uploadAsset($request->file('file'));
        } elseif ($request->assetType == 'url') {
            $assetUrl = $template->uploadAssetFromUrl($request->url);
        } elseif ($request->assetType == 'base64') {
            $assetUrl = $template->uploadAssetFromBase64($request->url_base64);
        }

        return response()->json([
            'url' => $assetUrl
        ]);
    }
    
    /**
     * Create template / temlate selection.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function builderCreate(Request $request)
    {
        $template = new Template();
        $template->name = trans('messages.untitled_template');
        
        // authorize
        if (!$request->user()->admin->can('create', Template::class)) {
            return $this->notAuthorized();
        }
        
        // Gallery
        $templates = Template::where('customer_id', '=', null);
        
        // validate and save posted data
        if ($request->isMethod('post')) {
            $currentTemplate = Template::findByUid($request->template);

            // create from template
            $template = $currentTemplate->copy([
                'name' => $request->name,
                'admin_id' => $request->user()->admin->id,
            ]);

            // save
            $template->save();
            
            return redirect()->action('Admin\TemplateController@builderEdit', $template->uid);
        }

        return view('admin.templates.builder.create', [
            'template' => $template,
            'templates' => $templates,
        ]);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function builderTemplates(Request $request)
    {
        // authorize
        if (!$request->user()->admin->can('create', Template::class)) {
            return $this->notAuthorized();
        }

        // category
        $category = \Acelle\Model\TemplateCategory::findByUid($request->category_uid);
        
        // sort, pagination
        $templates = $category->templates()->search($request->keyword)
            ->orderBy($request->sort_order, $request->sort_direction)
            ->paginate($request->per_page);

        return view('admin.templates.builder.templates', [
            'templates' => $templates,
        ]);
    }
    
    /**
     * Change template from exist template.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function builderChangeTemplate(Request $request, $uid, $change_uid)
    {
        // Generate info
        $template = Template::findByUid($uid);
        $changeTemplate = Template::findByUid($change_uid);

        // authorize
        if (!$request->user()->admin->can('update', $template)) {
            return $this->notAuthorized();
        }
        
        $template->changeTemplate($changeTemplate);
    }

    /**
     * Update template thumb.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateThumb(Request $request, $uid)
    {
        $template = Template::findByUid($uid);
        
        // authorize
        if (!$request->user()->admin->can('update', $template)) {
            return $this->notAuthorized();
        }

        if ($request->isMethod('post')) {
            // make validator
            $validator = \Validator::make($request->all(), [
                'file' => 'required',
            ]);

            // redirect if fails
            if ($validator->fails()) {
                return response()->view('templates.updateThumb', [
                    'template' => $template,
                    'errors' => $validator->errors(),
                ], 400);
            }
            
            // update thumb
            $template->uploadThumbnail($request->file);

            return response()->json([
                'status' => 'success',
                'message' => trans('messages.template.thumb.uploaded'),
            ], 201);
        }

        return view('admin.templates.updateThumb', [
            'template' => $template,
        ]);
    }

    /**
     * Update template thumb url.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateThumbUrl(Request $request, $uid)
    {
        $template = Template::findByUid($uid);
        
        // authorize
        if (!$request->user()->admin->can('update', $template)) {
            return $this->notAuthorized();
        }

        if ($request->isMethod('post')) {
            // make validator
            $validator = \Validator::make($request->all(), [
                'url' => 'required|url',
            ]);

            // redirect if fails
            if ($validator->fails()) {
                return response()->view('templates.updateThumbUrl', [
                    'template' => $template,
                    'errors' => $validator->errors(),
                ], 400);
            }
            
            // update thumb
            $template->uploadThumbnailUrl($request->url);

            return response()->json([
                'status' => 'success',
                'message' => trans('messages.template.thumb.uploaded'),
            ], 201);
        }

        return view('admin.templates.updateThumbUrl', [
            'template' => $template,
        ]);
    }

    /**
     * Template categories.
     *
     * @return \Illuminate\Http\Response
     */
    public function categories(Request $request, $uid)
    {
        $template = Template::findByUid($uid);
        
        // authorize
        if (!$request->user()->admin->can('update', $template)) {
            return $this->notAuthorized();
        }

        if ($request->isMethod('post')) {
            foreach ($request->categories as $key => $value) {
                $category = \Acelle\Model\TemplateCategory::findByUid($key);
                if ($value == 'true') {
                    $template->addCategory($category);
                } else {
                    $template->removeCategory($category);
                }
            }
        }

        return view('admin.templates.categories', [
            'template' => $template,
        ]);
    }

    public function createTemplate(Request $request)
    {
        // authorize
        if (!$request->user()->customer->can('create', Template::class)) {
            return $this->notAuthorized();
        }

        //dd($request->all());
        $template = new Template();
        $template->name = trans('messages.untitled_template');
    
        return view('admin.templates.email.create', [
            'template' => $template,
        ]);
    }

    public function storeTemplate(Request $request)
    {
        // authorize
        if (!$request->user()->customer->can('create', Template::class)) {
            return $this->notAuthorized();
        }

        $template = Template::saveTemplate($request);        
    
        return redirect('admin/templates');
    }

    public function editTemplate(Request $request,$id)
    {
        // authorize
        if (!$request->user()->customer->can('create', Template::class)) {
            return $this->notAuthorized();
        }

        $template = Template::findByUid($id);
    
        return view('admin.templates.email.edit',[
            'template' => $template,
        ]);
    }

    public function updateTemplate(Request $request,$id)
    {
        // authorize
        if (!$request->user()->customer->can('create', Template::class)) {
            return $this->notAuthorized();
        }

        $template = Template::updateTemplate($id,$request);

        return redirect('admin/templates');
    }
}
