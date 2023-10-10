@extends('layouts.frontend')

@section('title', trans('messages.feedback_log'))

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/interactions.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/touch.min.js') }}"></script>

    <script type="text/javascript" src="{{ URL::asset('js/listing.js') }}"></script>
@endsection

@section('page_header')

    <div class="page-title">
        <ul class="breadcrumb breadcrumb-caret position-right">
            <li><a href="{{ url("/") }}">{{ trans('messages.home') }}</a></li>
            <li>Reports</li>
            <li><a href="javascript:void(0);">Marked as Spam</a></li>
        </ul>
        <h1>
            <span class="text-semibold"><i class="fa fa-file-text-o"></i> Marked as Spam</span>
        </h1>
    </div>

@endsection

@section('content')

    <form class="listing-form"
        data-url="{{ action('FeedbackLogController@listing') }}"
        per-page="{{ Acelle\Model\FeedbackLog::$itemsPerPage }}"
    >
        <div class="row top-list-controls">
            <div class="col-md-9">
                @if ($items->count() > 0)
                    <table class="table pml-table1 table-log"
                        current-page="{{ empty(request()->page) ? 1 : empty(request()->page) }}"
                    >
                        <thead>
                            <tr>
                                <th>{{ trans('messages.recipient') }}</th>
                                <th>{{ trans('messages.feedback_type') }}</th>
                                <th>{{ trans('messages.raw_feedback_content') }}</th>
                                <th>{{ trans('messages.campaign') }}</th>
                                <th>{{ trans('messages.sending_server') }}</th>
                                <th>{{ trans('messages.created_at') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $key => $item)
                                <tr>
                                    <td>
                                        <span class="no-margin kq_search">{{ $item->trackingLog->subscriber->email }}</span>
                                        <span class="text-muted second-line-mobile">{{ trans('messages.recipient') }}</span>
                                    </td>
                                    <td>
                                        <span class="no-margin kq_search">{{ $item->feedback_type }}</span>
                                        <span class="text-muted second-line-mobile">{{ trans('messages.feedback_type') }}</span>
                                    </td>
                                    <td>
                                        <span class="no-margin kq_search">{{ $item->raw_feedback_content }}</span>
                                        <span class="text-muted second-line-mobile">{{ trans('messages.raw_feedback_content') }}</span>
                                    </td>
                                    <td>
                                        <span class="no-margin kq_search">{{ is_null($item->trackingLog->campaign) ? 'N/A' : $item->trackingLog->campaign->name }}</span>
                                        <span class="text-muted second-line-mobile">{{ trans('messages.campaign') }}</span>
                                    </td>
                                    <td>
                                        <span class="no-margin kq_search">{{ $item->trackingLog->sendingServer->name }}</span>
                                        <span class="text-muted second-line-mobile">{{ trans('messages.sending_server') }}</span>
                                    </td>
                                    <td>
                                        <span class="no-margin kq_search">{{ Tool::formatDateTime($item->created_at) }}</span>
                                        <span class="text-muted second-line-mobile">{{ trans('messages.created_at') }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <div class="pml-table-container table-responsive">
        </div>
    </form>
<script type="text/javascript">
    $(document).ready(function() {
        $('.pml-table1').DataTable( {
            dom: 'Bfrtip',
            buttons: [
                'copyHtml5',
                'excelHtml5',
                'csvHtml5',
                'pdfHtml5'
            ]
        } );
    } );


</script>
@endsection
