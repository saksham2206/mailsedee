<div class="subsection mt-4 pt-4">
    <iframe class="chart2-iframe mt-4 pt-4" src="{{ action('CampaignController@chart2', ['uid' => $campaign->uid]) }}"></iframe>
    <script>
        var resendPopup = new Popup();
    </script>
</div>