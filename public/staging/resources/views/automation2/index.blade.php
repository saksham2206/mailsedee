@extends('layouts.frontend')

@section('title', trans('messages.Automations'))

@section('page_script')

    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/interactions.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/touch.min.js') }}"></script>
		
	<script type="text/javascript" src="{{ URL::asset('js/listing.js') }}"></script>		
@endsection

@section('page_header')

    <div class="page-title">				
        <ul class="breadcrumb breadcrumb-caret position-right">
            <li><a href="{{ url("/") }}">{{ trans('messages.home') }}</a></li>
            <li class="active">{{ trans('messages.Automations') }}</li>
        </ul>
        <h1>
            <span class="text-semibold"><i class="fe-clock"></i> {{ trans('messages.Automations') }}</span>
        </h1>
        			
    </div>
    @php
    $bounceData = checkBounceAdded();
    @endphp
    @if(count($bounceData)>0)
        <div class="alert alert-warning">
            <p>Please Add Bounce Handler FIrst.</p> 
            <a href="{{url('bounce_handlers/create')}}" class="btn-primary">Add</a>
            <button class="btn-danger" onclick="declineBounceHandler();" >Decline</button>
        </div>
    @endif  
    @if(config('queue.default') == 'async')
        <div class="alert alert-warning">
            {{ trans('messages.automation_not_work_with_async') }}
        </div>
    @endif

@endsection

@section('content')

      <div class="page-title">
                           
          <h1>
             <span class="text-semibold">{!! trans('messages.frontend_dashboard_hello', ['name' => Auth::user()->displayName()]) !!}</span>
          </h1>
          <p>{!! trans('messages.frontend_dashboard_welcome') !!}</p>
     </div>
    <form class="listing-form"
        data-url="{{ action('Automation2Controller@listing') }}"
        per-page="{{ \Acelle\Model\Automation2::ITEMS_PER_PAGE }}"					
    >				
        <div class="row top-list-controls">
            <div class="col-md-10">
                <div class="filter-box">
                    <div class="btn-group list_actions hide">
                        <button type="button" class="btn btn-xs btn-grey-600 dropdown-toggle" data-toggle="dropdown">
                            {{ trans('messages.actions') }} <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a data-method="PATCH" link-confirm="{{ trans('messages.enable_automations_confirm') }}"
                                    href="{{ action('Automation2Controller@enable') }}"
                                >
                                    <!-- <i class="icon-checkbox-checked2"></i> --> {{ trans('messages.enable') }}
                                </a>
                            </li>
                            <li><a data-method="PATCH" link-confirm="{{ trans('messages.disable_automations_confirm') }}" href="{{ action('Automation2Controller@disable') }}"><!-- <i class="icon-checkbox-unchecked2"></i> --> {{ trans('messages.disable') }}</a></li>
                            <li>
                                <a data-method='delete' delete-confirm="{{ trans('messages.delete_automations_confirm') }}" href="{{ action('Automation2Controller@delete') }}">
                                <!-- <i class="icon-trash"></i> --> {{ trans('messages.delete') }}</a>
                            </li>
                        </ul>
                    </div>
                    <div class="checkbox inline check_all_list">
                        <label>
                            <input type="checkbox" class="styled check_all">
                        </label>
                    </div>
                    <span class="filter-group">
                        <span class="title text-semibold text-muted">{{ trans('messages.sort_by') }}</span>
                        <select class="select" name="sort-order">
                            <option value="created_at">{{ trans('messages.created_at') }}</option>
                            <option value="name">{{ trans('messages.name') }}</option>                                
                        </select>										
                        <button class="btn btn-xs sort-direction" rel="desc" data-popup="tooltip" title="{{ trans('messages.change_sort_direction') }}" type="button" class="btn btn-xs">
                            <i class="icon-sort-amount-desc"></i>
                        </button>
                    </span>
                    <span class="text-nowrap">
                        <input name="search_keyword" class="form-control search" placeholder="{{ trans('messages.type_to_search') }}" />
                        <i class="icon-search4 keyword_search_button"></i>
                    </span>
                </div>
            </div>
            <div class="col-md-2 text-right">
                <a href="{{ action("Automation2Controller@create") }}" type="button" class="btn bg-info-800 create-automation2">
                    <i class="icon icon-plus2"></i> {{ trans('messages.automation.create') }}
                </a>
            </div>
        </div>
        
        <div class="pml-table-container table-responsive">
            
            
            
        </div>
    </form>
        
    <script>
        var createAutomationPopup = new Popup();
    
        function showCreateCampaignPopup() {
            var url = '{{ action("Automation2Controller@create") }}';
                  
            createAutomationPopup.load(url);            
        }
        
        $(document).ready(function() {
        
            $('.create-automation2').click(function(e) {
                e.preventDefault();
                
                showCreateCampaignPopup();
            });
        
        });

        function declineBounceHandler(){
            var result = confirm("Want to delete?");
            if (result) {
                $.ajax({
                    url:'{{url("automation/declineBounce")}}',
                    success:function(response){
                        window.location.reload();
                    }
                })
            }

        }
        
    </script>
@endsection
