@extends('layouts.popup.large')

@section('content')
	<div class="row">
        <div class="col-md-12">
            <h3 class="mb-3">{{ trans('messages.automation.choose_an_operation') }}</h3>
            <p>{{ trans('messages.automation.choose_an_operation.desc') }}</p>
                
            <div class="box-list mt-3">
                <div class="box-list mt-40">
                    @foreach ($types as $type)
                        <a class="box-item operation-select-but"
                            data-key="{{ $type }}"	
                            data-url="{{ action('Automation2Controller@operationCreate', [
                                'uid' => $automation->uid,
                                'operation' => $type,
                                'operation_id' => request()->id,
                            ]) }}"					
                        >							
                            <h6 class="d-flex align-items-center text-center justify-content-center">
                                <i class="material-icons-outlined mr-2">{{ trans('messages.automation.operation.icon.' . $type) }}</i>
                                {{ trans('messages.automation.operation.' . $type) }}</h6>
                            <p>{{ trans('messages.automation.operation.' . $type . '.desc') }}</p>
                        </a>
                    @endforeach                
                </div>
            </div>
        </div>
    </div>
    <script>
        // set back
        popup.back = function() {
            var backUrl = popup.url;
            popup.load(backUrl);
        };
        
        $('.operation-select-but').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            var url = $(this).attr('data-url');

            popup.load(url);
        });
    </script>

@endsection
