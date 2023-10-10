@extends('layouts.popup.small')

@section('content')  
    <div class="d-flex">
        <div class="pr-4">
            <h4 class="mt-0">Little Kitty Store</h4>
            <p>Overview of your campaign performance
                <br>Click on a number to see its details</p>
        </div>
        <div class="ml-auto pl-4">
            <button class="btn btn-secondary">Refresh</button>
        </div>
    </div>  
           

    <div class="">
        <div class="email-row d-flex align-items-center">
            <div class="mr-3 d-flex align-items-center">
                <span class="material-icons-outlined">
                    pause_circle
                </span>
            </div>
            <div class="content">
                <div class="mb-1">Email title: <strong class="font-weight-semibold">This is an email Title</strong></div>
                <div class="small text-muted">
                    Sent <strong class="font-weight-semibold">12 hours</strong> after items are left in cart
                </div>
            </div>
            <div class="stats ml-auto d-flex">
                <div class="prate">
                    <div class="percent">0.0%</div>
                    <div class="text-muted small">Opens</div>
                </div>
                <div class="prate ml-4 pl-2">
                    <div class="percent">0.0%</div>
                    <div class="text-muted small">Click</div>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-boxes mt-4 d-flex justify-content-space-between">
        <div class="tab-box bg-yellow-light" onclick="timelinePopup.load('{!!
                action('Automation2Controller@cartList', $automation->uid)
            !!}')">
            <div class="number mb-2">
                14 / $120
            </div>
            <div class="desc small">
                items/total value currently in carts, from 3 buyer
            </div>
        </div>
        <div class="tab-box" onclick="timelinePopup.load('{!!
            action('Automation2Controller@cartItems', $automation->uid)
        !!}')">
            <div class="number mb-2">
                75
            </div>
            <div class="desc small">
                Notification emails sent
                during the last 3 days
            </div>
        </div>
        <div class="tab-box" onclick="timelinePopup.load('{!!
            action('Automation2Controller@cartItems', $automation->uid)
        !!}')">
            <div class="number mb-2">
                $1,200
            </div>
            <div class="desc small">
                Converted revenue
                from campaign
            </div>
        </div>
    </div>

    <div class="mt-4 pt-3">
        <h5 class="font-weight-semibold">Monthly performance</h5>
        <canvas id="myChart" width="400" height="200"></canvas>
        <script>
            $(document).ready(function(){
                var ctx = document.getElementById('myChart').getContext('2d');
                var myChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'],
                        datasets: [{
                            label: '# Opens',
                            data: [4, 7, 3, 5, 2, 3],
                            backgroundColor: [
                                'rgba(255, 99, 132, 1)'
                            ],
                            borderColor: [
                                'rgba(255, 99, 132, 1)'
                            ]
                        },
                        {
                            label: '# Clicks',
                            data: [5, 6, 6, 4, 2, 1],
                            backgroundColor: [
                                'rgba(54, 162, 235, 1)'
                            ],
                            borderColor: [
                                'rgba(54, 162, 235, 1)'
                            ]
                        },
                        {
                            label: '# Emails sent',
                            data: [1, 3, 4, 2, 5, 2],
                            backgroundColor: [
                                'rgba(255, 206, 86, 1)'
                            ],
                            borderColor: [
                                'rgba(255, 206, 86, 1)'
                            ]
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: false
                            }
                        }
                    }
                });
            });
        </script>
    </div>
@endsection