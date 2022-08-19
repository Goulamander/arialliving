@extends('layouts.dashboard')

@section('title', $item->title.' - Items | '.config('app.name'))

@section('content') 
    @include('layouts.messagesTemplate')
    <div class="row clearfix with_banner">
        @php 

        $style = '';
        if(isset($item->images[array_key_first($item->images)])) {
            $style = 'style="background-image: url('.\Storage::url($item->images[array_key_first($item->images)]).')"';
        }

        @endphp 
        <div class="single_top_banner" {!! $style !!}>
            <div class="container">
                <div class="buttons">
                    <button type="button" data-toggle="modal" class="btn btn-round btn-outline" data-target="#mod-edit-gallery">Edit Gallery</button>
                    <a href="{{route('app.preview.item', [$item->typeStr(), $item->id])}}" target="_blank" class="btn btn-round btn-outline btn-arrow">Preview <i class="material-icons">arrow_forward</i></a>
                    @if($item->status == \App\Models\BookableItem::$STATUS_DRAFT)
                        <button type="button" data-toggle="modal" id="publishItem" data-id="{{$item->id}}" class="btn btn-round btn-primary btn-arrow">Publish item <i class="material-icons">arrow_forward</i></button>
                    @endif
                </div>
            </div>
        </div>
       
        {{-- Side Card --}}
        <div class="col-lg-4 col-md-12">
            <div class="card">
                <div class="header">
                    <h2><strong>Item Profile</strong></h2>
                    <ul class="header-dropdown">
                        <li class="dropdown"> <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <i class="zmdi zmdi-more"></i> </a>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li><a href="#" data-toggle="modal" data-target="#mod-{{$item->typeStr()}}" class="md-trigger">Edit</a></li>
                                @if( ! $item->trashed() && Auth::user()->hasRole(['super-admin', 'admin']))
                                <li><a href="{{route('app.item.delete', $item->id)}}" class="actions" data-target="#mod-delete" data-reload="true">Delete Item</a></li>
                                @endif
                            </ul>
                        </li>
                    </ul>
                </div>
                <div class="body">
                    
                    <div class="row">
                        <div class="col-12 mb-3 profile-head">
                            <div class="image-thumb-uploader" data-name="file" data-path="{{$item->imagePath()}}" data-filename="@if($item->is_thumb){{$item->is_thumb}}@endif" data-process-type="thumbnail">
                                @if( $item->is_thumb ) 
                                <input type="hidden" data-type="local" value="{{ encrypt($item->getThumbWithoutDomain()) }}">
                                @endif
                            </div>
                            <h3 id="itemTitle" class="mb-0">{{$item->title}} {!! $item->getStatus(true) !!}</h3>
                            <span class="text-light">{{$item->category->name}}</span>
                        </div>
                    </div>

                    <h4>Building</h4>
                    @if( $item->building )
                        <div class="building-staff">
                            <h4>{{$item->building->name}}</h4>
                            <small>{{$item->building->suburb}}</small>
                        </div>
                    @else
                    <small>This item has not been attached to any building yet.</small>
                    @endif

                    {{-- Room/Area item details --}}
                    @if($item->isRoom())
                        @include('app.item.partials.roomCard')
                    @endif

                    {{-- Hire item details --}}
                    @if($item->isHire())
                        @include('app.item.partials.hireCard')
                    @endif

                    {{-- Event item details --}}
                    @if($item->isEvent())
                        @include('app.item.partials.eventCard')
                    @endif

                    {{-- Service Item details --}}
                    @if($item->isService())
                        @include('app.item.partials.serviceCard')
                    @endif
                </div>

            </div>
        </div>

        {{-- Content --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="body">
                    <ul class="nav nav-modal mb-3" role="tablist">
                        @php $tab_active = 'active' @endphp
                        @if( $item->isService() )
                            @php $tab_active = ''; @endphp
                        <li class="nav-item">
                            <a class="nav-link active" id="service-items-tab" data-toggle="pill" href="#service-items" role="tab" aria-selected="true">Items</a>
                        </li>
                        @endif
                        <li class="nav-item">
                            <a class="nav-link {{$tab_active}}" id="item-content-tab" data-toggle="pill" href="#item-content" role="tab" aria-selected="true">Content</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="item-instructions-tab" data-toggle="pill" href="#item-instructions" role="tab" aria-selected="true">@if($item->isService()) Order  @else Booking @endif Instructions</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="item-files-tab" data-toggle="pill" href="#item-files" role="tab" aria-selected="false">Terms &amp; Conditions</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="admin-comments-tab" data-toggle="pill" href="#admin-comments" role="tab" aria-selected="false">Admin Comments</a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        @php $tab_active = 'show active' @endphp
                        
                        @if( $item->isService() )
                            @php $tab_active = ''; @endphp
                            <div id="service-items" class="tab-pane fade show active" role="tabpanel" aria-labelledby="service-items-tab">
                                <div class="row mb-3">
                                    <div class="col">
                                        <h3 class="mt-3">Service items</h3>
                                    </div>
                                    <div class="col-4">
                                        <button type="button" data-toggle="modal" data-target="#mod-line-item" class="btn btn-primary btn-sm btn-round float-right md-trigger">Add Service Item</button>
                                    </div>
                                </div>
                                <table id="data_table" class="table data_table">
                                    <thead>
                                        <tr>
                                            <th style="width: 10%">ID</th>
                                            <th style="width: 30%">Name</th>
                                            <th style="width: 15%">Price</th>
                                            <th style="width: 15%">Status</th>
                                            <th style="width: 15%">Created at</th>
                                            <th style="width: 15%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        @endif

                        <div id="item-content" class="tab-pane fade {{$tab_active}}" role="tabpanel" aria-labelledby="item-content-tab">
                            @include('app.item.partials.contentEditor')
                        </div>
                        <div id="item-instructions" class="tab-pane fade" role="tabpanel" aria-labelledby="item-instructions-tab">
                            @include('app.item.partials.instructionEditor')
                        </div>
                        <div id="item-files" class="tab-pane fade" role="tabpanel" aria-labelledby="item-files-tab">
                            <div class="row">
                                <div class="col-12">
                                    <h3 class="mt-4 float-left">Terms &amp; Conditions</h3>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                 
                                    <h4>Upload one or multiple documents that residents have to agree on</h4>
                                    <input type="file" class="_attachments" name="file" data-name="file" data-title="PDF Attachments" data-path="{{$item->termsPath()}}" data-process-type="pdf-attachment" multiple=""/>

                                    <div id="terms-list" class="_pdf_list">
                                        @if($item->terms)
                                            @foreach($item->terms as $term)
                                                <div data-file="{{$term->file}}" class="_pdf">
                                                    <a data-iframe="true" href="{{$term->path}}" class="term-link">{{$term->filename}}</a>
                                                    <div class="dropdown dropleft _actions">
                                                        <a class="no-btn" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <i class="zmdi zmdi-more"></i>
                                                        </a>
                                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                                            <button type="button" class="dropdown-item _rename">Rename</button>
                                                            <button type="button" class="dropdown-item _delete">Delete</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <span class="empty">No terms upload yet</span>
                                        @endif
                                    </div>
                                    {{-- Signature required --}}
                                    <form method="POST" action="{{route('app.item.storeSingle', $item->id)}}" autocomplete="off">
                                        <div class="form-group mt-4">
                                            <div class="checkbox">
                                                <input type="checkbox" id="is_signature_required" name="is_signature_required" value="1" @if($item->is_signature_required) checked @endif>
                                                <label for="is_signature_required">Is signature required for this item?</label>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div id="admin-comments" class="tab-pane fade" role="tabpanel" aria-labelledby="admin-comments-tab">
                            <h4>Admin Comments</h4>
                            @include('app._partials.comments', ['comments' => $item->comments])
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection


@section('modals')
    {{-- Edit Gallery --}}
    @include('app.item.modals.gallery')
    {{-- Edit item --}}
    @php $include_key = 'app.item.modals.'.$item->typeStr(); @endphp
    @include($include_key)
    {{-- Delete --}}
    @include('app._partials.modals.confirmDelete')
    {{-- Edit File --}}
    @include('app.item.modals.renameFile')
    
    {{-- Add/Edit line items --}}
    @if( $item->isService() )
        @include('app.item.modals.lineItem')
    @endif
@endsection


@section('scripts')
    {{-- Line items table --}}
    @if( $item->isService() )
    <script type="text/javascript">
        function init_DataTable() {
            return $('#data_table').dataTable({
                buttons: ['csv'],
                iDisplayLength: 50,
                dom: '<"export_buttons"B>tip',
                ajax: {
                    method: 'POST',
                    url: '{{ route("app.item.lineItems.list", $item->id) }}',
                },
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'name', name: 'name'},
                    {data: 'price', name: 'price'},
                    {data: 'status', name: 'status'},
                    {data: 'created_date', name: 'created_date'},
                    {data: 'actions', name: 'actions', orderable: false}
                ],
                initComplete: function () {

                    this.addClass('ready')

                    const tr = document.createElement('tr')
                    
                    this.api().columns().every(function (index) {
                        var column = this
                        var td = document.createElement('th')

                        switch(index) {

                            case 3:
                                var choices = [
                                        {val : '', text: 'All'},
                                        {val : {{\App\Models\LineItem::$STATUS_ACTIVE}}, text: 'Active'},
                                        {val : {{\App\Models\LineItem::$STATUS_INACTIVE}}, text: 'Inactive'},
                                    ]
                            
                                var select = $('<select>').addClass('form-control').appendTo($(td))
                                    $(choices).each(function() {
                                        select.append($("<option>").attr('value',this.val).text(this.text))
                                    })
                                    select.on('change', function(){
                                        column.search($(this).val()).draw()
                                    })
                                    select.val('').change()
                                break;

                            // No filter  
                            case 4:
                            case 5:
                                break;
                            //
                            default:
                                var input = $('<input>').attr('type', 'text')
                                $(input).addClass('form-control').appendTo($(td))
                                .on('keyup', function () {
                                    column.search($(this).val()).draw()
                                })
                                break
                        }
                        $(tr).append($(td))
                    })
                    $(tr).appendTo(this.find('thead'))
                },
                order: [[ 0, "desc" ]],
            })
        }
        window.dataTable = init_DataTable()

        // disable service of external role
        const isExternal = !!'{{Auth::user()->isExternal()}}';
        if(isExternal) {
            if($('select[name=building_id]') && $('select[name=building_id]').length > 0){
                $('select[name=building_id]').prop('disabled', true);
            }
            if($('select[name=category_id]') && $('select[name=category_id]').length > 0){
                $('select[name=category_id]').prop('disabled', true);
            }
            if($('select[name=assign_to_user_id]') && $('select[name=assign_to_user_id]').length > 0){
                $('select[name=assign_to_user_id]').prop('disabled', true);
                $('._assign_to_user_id').css('display', 'none');
                // $('._category_id').css('display', 'none');
            }
            if($('input[name=hide_cart_functionality]') && $('input[name=hide_cart_functionality]').length > 0){
                $('input[name=hide_cart_functionality]').prop('disabled', true);
            }
        }
    </script>
    @endif
@endsection