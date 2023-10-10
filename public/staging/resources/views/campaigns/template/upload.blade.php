@extends('layouts.popup.small')

@section('content')
	<div class="row">
        <div class="col-md-12">
            <h2>{{ trans('messages.campaign.upload_custom_template') }}</h2>
                
            @include('campaigns.template._tabs')
            
            <div class="alert alert-info">
                {!! trans('messages.template_upload_guide', ["link" => 'https://s3.amazonaws.com/acellemail/newsletter-template-green.zip']) !!}
            </div>
            
            <form enctype="multipart/form-data" action="{{ action('CampaignController@templateUpload', $campaign->uid) }}" method="POST" class="ajax_upload_form form-validate-jquery">
                {{ csrf_field() }}

                <input type="hidden" name="name" value="{{ $campaign->name }}" />

                @include('helpers.form_control', ['required' => true, 'type' => 'file', 'label' => trans('messages.upload_file'), 'name' => 'file'])
				
                <div class="mt-20">
                    <button class="btn btn-primary bg-grey-600 mr-5">{{ trans('messages.upload') }}</button>
                </div>

            </form>
        </div>
    </div>
        
    <script>
        $('a.choose-template-tab').click(function(e) {
            e.preventDefault();
        
            var url = $(this).attr('href');
        
            templatePopup.load(url);
        });

        var builderSelectPopup = new Popup(null, undefined, {onclose: function() {
            window.location = '{{ action('CampaignController@template', $campaign->uid) }}';
        }});

        $('.ajax_upload_form').submit(function(e) {
            e.preventDefault();        
            var url = $(this).attr('action');
            var formData = new FormData($(this)[0]);

            addMaskLoading();

            // 
            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                async: true,
                cache: false,
                contentType: false,
                processData: false,
                statusCode: {
                    // validate error
                    400: function (res) {
                        alert('Something went wrong!');
                    }
                },
                error: function (data) {
                    alert(data.responseText);
                    removeMaskLoading();
                },
                success: function (response) {
                    removeMaskLoading();

                    // notify
                    notify('success', '{{ trans('messages.notify.success') }}', response.message);

                    builderSelectPopup.load(response.url);
                    templatePopup.hide();
                }
            });
        });
    </script>
@endsection