@extends('layouts.dashboard')

@section('title', $booking->getNumber().' | '.config('app.name'))

@section('content')
    @include('layouts.messagesTemplate')
    <div class="row clearfix with_banner">
        @php 

        $style = '';
        if(isset($booking->bookableItem->images[array_key_first($booking->bookableItem->images)])) {
            $style = 'style="background-image: url('.\Storage::url($booking->bookableItem->images[array_key_first($booking->bookableItem->images)]).')"';
        }

        @endphp 
        <div class="single_top_banner" {!! $style !!}>
            <div class="container"></div>
        </div>

        {{-- Side Card --}}
        <div class="col-lg-4 col-md-12">
            <div class="card">
                <div class="header">
                    <h2><strong>Booking Profile</strong></h2>
                    @if($booking->isEditable() && Auth::user()->hasRole(['super-admin', 'admin']))
                    <ul class="header-dropdown">
                        <li class="dropdown"> <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <i class="zmdi zmdi-more"></i> </a>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li><button type="button" onclick="myapp.booking.open({{$booking->id}}, false, true)">Edit Booking</button></li>
                                @if(! $booking->trashed() )
                                <li><a href="{{route('app.booking.delete', $booking->id)}}" class="actions" data-target="#mod-cancel" data-reload="true">Cancel booking</a></li>
                                @endif
                            </ul>
                        </li>
                    </ul>
                    @endif
                </div>
                <div class="body">
                    <div class="row">
                        <div class="col-12 mb-3 profile-head">
                            <h4 class="m-0">{{$booking->getNumber()}} {!! $booking->statusLabel() !!}</h4>
                            @if($booking->isHire())
                            <h3 class="mb-0">{{$booking->qty}} x <a href="{{route('app.item.show', $booking->bookableItem->id)}}" target="_blank">{{$booking->bookableItem->title}}</a></h3>
                            @else 
                            <h3 class="mb-0"><a href="{{route('app.item.show', $booking->bookableItem->id)}}" target="_blank">{{$booking->bookableItem->title}}</a></h3>
                            @endif
                            <span class="text-light">{{$booking->bookableItem->category->name}}</span>
                        </div>
                    </div>
                    <div class="building-staff">
                        <h4>
                            <a href="{{route('app.resident.show', $booking->user->id)}}" target="_blank">
                                {{$booking->user->fullName()}}
                                @if($booking->user->is_flagged)
                                    {!!\App\Models\User::getFlagLabel($booking->user->is_flagged_reason)!!}
                                @endif
                            </a>
                            <span>{{$booking->user->role->display_name}}</span>
                        </h4> 
                        @if($booking->user->mobile)
                        <a href="tel:{{str_replace(' ', '', $booking->user->mobile)}}" class="phone">{{$booking->user->mobile}}</a>
                        @endif
                        <a href="mailto:{{$booking->user->email}}" class="email">{{$booking->user->email}}</a>    
                        <hr>
                        <div class="row">
                            <div class="col-8">
                                <div class="clearfix"></div>
                                @if($booking->building->getThumb())
                                <span class="initials _bg float-left mt-1 mr-2" style="background-image: url({{$booking->building->getThumb()}})"></span>
                                @endif
                                <p>{{$booking->building->name}}</p>
                                <small class="float-left">{{$booking->building->suburb}}</small>
                            </div>
                            <div class="col-4">
                                <small class="text-muted">Unit No.</small>
                                @if($booking->user->isResident())
                                <p>{{$booking->user->building->first()->pivot->unit_no}}</p>
                                @else
                                <p>-</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    {{-- Include the details --}}
                    @include('app.booking.partials.details.'.$booking->typeLabel(true))
                    <hr>
                    <div class="row mt-4">
                        <div class="col-8">
                            <small class="text-muted">Booking Date/Time</small>
                            <p>{{dateFormat($booking->created_at)}} {{timeFormat($booking->created_at)}} <i class="icon-info icon-sm ml-1" data-tippy-content="Last edited on {{dateFormat($booking->updated_at)}} {{timeFormat($booking->updated_at)}}"></i></p>
                        </div>
                    </div>

                    @if($booking->booking_comments)
                    <hr>
                    <div class="row mt-4">
                        <div class="col-8">
                            <small class="text-muted">Resident Comments</small>
                            <p>{{$booking->booking_comments}}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>    
        </div>

        {{-- Content --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="body">

                    <ul class="nav nav-modal mb-3" role="tablist">
                        @php $active = 'active'; @endphp

                        {{-- Service: Order items tab --}}
                        @if( $booking->isService() )
                            <li class="nav-item">
                                <a class="nav-link {{$active}}" id="ordered-items-tab" data-toggle="pill" href="#ordered-items" role="tab" aria-selected="true">Order Items</a>
                            </li>
                            @php $active = ''; @endphp
                        @endif

                        {{-- Event: Attendees list tab --}}
                        @if( $booking->isEvent() )
                            <li class="nav-item">
                                <a class="nav-link {{$active}}" id="attendees-tab" data-toggle="pill" href="#attendees" role="tab" aria-selected="true">Attendees</a>
                            </li>
                            @php $active = ''; @endphp
                        @endif

                        {{-- Payment tab --}}
                        @if( ! $booking->isService() && ($booking->total && $booking->total > 0) )
                            <li class="nav-item">
                                <a class="nav-link {{$active}}" id="transactions-tab" data-toggle="pill" href="#transactions" role="tab" aria-selected="false">Payment</a>
                            </li>
                            @php $active = ''; @endphp
                        @endif

                        {{-- Terms and Signature tab --}}
                        @if( $booking->accepted_terms || $booking->signature )
                            <li class="nav-item">
                                <a class="nav-link {{$active}}" id="accepted-terms-tab" data-toggle="pill" href="#accepted-terms" role="tab" aria-selected="false">Accepted Terms</a>
                            </li>
                            @php $active = ''; @endphp
                        @endif

                        {{-- Admin Comments tab --}}
                        @if (Auth::user()->hasRole(['super-admin', 'admin']))
                        <li class="nav-item">
                            <a class="nav-link {{$active}}" id="admin-comments-tab" data-toggle="pill" href="#admin-comments" role="tab" aria-selected="false">Admin Comments</a>
                        </li>
                        @endif
                    </ul> 

                    @php $show = 'show active'; @endphp
                    <div class="tab-content">

                        {{-- Service: Order Summary --}}
                        @if( $booking->isService() )
                        <div id="ordered-items" class="tab-pane fade {{$show}}" role="tabpanel" aria-labelledby="ordered-items-tab">  
                            <h4>Ordered items</h4>
                           
                            @include('app.booking.partials.paymentDetails.'.$booking->typeLabel(true))
            
                            @if(!$booking->transactions->isEmpty())
                                <h4>Transactions</h4>
                                <div class="transactions">
                                    @foreach($booking->transactions as $transaction) 
                                        @include('app.booking.partials.transaction')
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        @php $show = ''; @endphp
                        @endif

                        {{-- Event: Attendees list --}}
                        @if( $booking->isEvent() )
                        <div id="attendees" class="tab-pane fade {{$show}}" role="tabpanel" aria-labelledby="attendees-tab">  
                            
                            <h4>Event details</h4>
                            <div class="event_stats mb-4">
                                <div class="row">
                                    <div class="col-4">
                                        <small class="text-muted">Total number of attendees</small>
                                        @if($booking->bookableItem->event->attendees_limit && $booking->bookableItem->event->attendees_limit > 0)
                                            <p>{{(int) $total_attendees + (int) $booking->event->attendees_num}}/{{$booking->bookableItem->event->attendees_limit}}</p>
                                        @else
                                            <p>{{(int) $total_attendees + (int) $booking->event->attendees_num}}</p>
                                        @endif
                                    </div>
                                    @if($booking->bookableItem->is_free == false)
                                    <div class="col-4">
                                        <small class="text-muted">Total amount</small>
                                        <p>{{priceFormat($total_paid + $booking->total)}}</p>
                                    </div>
                                    @endif
                                    <div class="col-4"></div>
                                </div>
                            </div>

                            <h4>Attendees</h4>
                            <table class="data_table ready">
                                <thead>
                                    <tr>
                                        <th>Booking No.</th>
                                        <th>Name</th>
                                        <th>No. of Guests</th>
                                        <th>Booking Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="highlight">
                                        <td>{{$booking->getNumber()}}</td>
                                        <td>{{$booking->user->fullName()}}</td>
                                        <td>{{$booking->event->getAttendeesNumber()}}</td>
                                        <td>{{dateFormat($booking->created_at)}}</td>
                                        <td>{!!$booking->statusLabel()!!}</td>
                                    </tr>
                                    @if($booking->attendees)
                                        @foreach($booking->attendees as $attendee_booking)
                                        <tr>
                                            <td>
                                                <a href="#" onclick="myapp.booking.open({{$attendee_booking->id}}, false, true)" class="title">
                                                    <span>{{$attendee_booking->getNumber()}}</span>
                                                </a>
                                            </td>
                                            <td>{{$attendee_booking->user->fullName()}}</td>
                                            <td>{{$attendee_booking->event->getAttendeesNumber()}}</td>
                                            <td>{{dateFormat($attendee_booking->created_at)}}</td>
                                            <td>{!!$attendee_booking->statusLabel()!!}</td>
                                        </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>

                        </div>
                        @php $show = ''; @endphp
                        @endif

                        {{-- Payments --}}
                        @if( ! $booking->isService() && ($booking->total && $booking->total > 0) )
                        <div id="transactions" class="tab-pane fade {{$show}}" role="tabpanel" aria-labelledby="transactions-tab">  
                            <h4>Transactions</h4>
                            
                            @include('app.booking.partials.paymentDetails.'.$booking->typeLabel(true))
                        
                            @if(!$booking->transactions->isEmpty())
                            <h4>Transactions</h4>
                            <div class="transactions">
                                @foreach($booking->transactions as $transaction) 
                                    @include('app.booking.partials.transaction')
                                @endforeach
                            </div>
                            @else 
                            
                                @if($booking->status == \App\Models\Booking::$STATUS_ACTIVE)
                                <div class="payment_notes">
                                    <h4 class="m-0">Payment not yet processed</h4>
                                    <hr>
                                    <p>Payment will be charged on <b>{{dateFormat($booking->cancellation_cutoff_date)}}</b></p>
                                </div>
                                @endif

                            @endif
                        </div>
                        @php $show = ''; @endphp
                        @endif

                        {{-- Accepted Terms ans Signature --}}
                        @if( $booking->accepted_terms || $booking->signature )
                        <div id="accepted-terms" class="tab-pane fade {{$show}}" role="tabpanel" aria-labelledby="accepted-terms-tab">  
                            
                            @php $accepted_terms = json_decode($booking->accepted_terms); @endphp
                            
                            @if($accepted_terms)
                            <h4>Accepted Terms</h4>
                            <table class="data_table ready">
                                <thead>
                                    <tr>
                                        <th>Term</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>  
                                @foreach($accepted_terms as $term)
                                    <tr>
                                        <td>{{$term}}</td>
                                        <td>{{dateFormat($booking->created_at)}} - {{timeFormat($booking->created_at)}}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            @endif
                            
                            @if($booking->signature)
                                <h4>Signature</h4>
                                <div class="signatureCanvas" style="background-image: url({{decrypt($booking->signature)}})"></div>
                            @endif
                            
                        </div>
                        @php $show = ''; @endphp
                        @endif

                        {{-- Admin Comments --}}
                        <div id="admin-comments" class="tab-pane fade {{$show}}" role="tabpanel" aria-labelledby="admin-comments-tab">
                            <h4>Admin Comments</h4>
                            @include('app._partials.comments', ['comments' => $booking->comments])
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection


@section('modals')
    {{-- View booking --}}
    @include('app.booking.card')
    {{-- Cancel booking --}}
    @include('app.bookings.modals.cancel')

    @if( ! $booking->transactions->isEmpty() )
        {{-- Refund transaction --}}
        @include('app.booking.modals.refund')
        {{-- Release bond --}}
        @include('app.booking.modals.releaseBond')
    @endif
@endsection


@section('scripts')
@endsection