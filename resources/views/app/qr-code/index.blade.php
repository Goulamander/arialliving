@extends('layouts.dashboard')

@section('title', 'QR Codes | '.config('app.name'))

@section('content')
    @include('layouts.messagesTemplate')

    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card">

                <div class="header pb-0">
                    <h2><strong>QR Codes</strong> list</h2>
                    <div class="row mt-2">
                        <div class="col-sm-8">
                            <ul class="nav nav-modal mt-3">
                                <li class="nav-item active">
                                    <a href="/admin/qr-codes" class="nav-link @if($tab == '') active @endif">Active</a>
                                </li>
                                <li class="nav-item">
                                    <a href="/admin/qr-codes/archive" class="nav-link @if($tab == 'archive') active @endif">Archive</a>
                                </li>
                            </ul>
                        </div>
                        <div class="col-sm-4">
                            <button data-toggle="modal" data-target="#mod-qr-codes" type="button" class="btn btn-primary btn-round float-right md-trigger">Add QR Code</button>
                        </div>
                    </div>
                </div>

                <div class="body">
                    <table class="table data_table users">
                        <thead>
                            <tr>
                                <th style="width: 20%;">Name</th>
                                <th style="width: 25%;">Content</th>
                                <th style="width: 15%;">QR Code</th>
                                <th style="width: 10%">Status</th>
                                <th style="width: 10%">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

@endsection


@section('scripts')
    <script type="text/javascript">

        /**
         * init dataTables
         */
        function init_DataTable() 
        {
            return $('.table').dataTable({
                buttons: [],
                // iDisplayLength: 100,
                // dom: 'Bfrtip',
                ajax: {
                    url: '{{ route("app.qr-code.list", $tab) }}',
                    method: 'POST'
                },
                columns: [
                    {data: 'name', name: 'name'},
                    {data: 'content', name: 'content'},
                    {data: 'qrcode', name: 'qrcode'},
                    {data: 'type', name: 'type'},
                    {data: 'actions', name: 'actions', orderable: false}
                ],
                initComplete: function () {
                    //
                    this.addClass('ready')

                    // Create filters
                    const tr = document.createElement('tr')
                    
                    this.api().columns().every(function (index) {
                        var column = this
                        var td = document.createElement('th')

                        switch(index) {

                            // Role
                            case 3:
                                var choices = [
                                    {val : '', text: 'All'},
                                    {val : {{\App\Models\QrCode::$STATUS_ACTIVE}}, text: 'Active'},
                                    {val : {{\App\Models\QrCode::$STATUS_INACTIVE}}, text: 'In-Active'},
                                ]
                                
                                var select = $('<select>').addClass('form-control').appendTo($(td))
                                    $(choices).each(function() {
                                        select.append($("<option>").attr('value',this.val).text(this.text))
                                    })
                                    select.on('change', function(){
                                        column.search($(this).val()).draw()
                                    })
                                    select.val('').change()
                                break

                            // No filter  
                             case 2:
                                 break;   
                             case 4:
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
                    // end filters
                },
                order: [[ 0, "asc" ]],
            })
        }
        //
        window.dataTable = init_DataTable()


        // print qr-code click
        $(document).on('click', '.print_qr_code', function() {
            const _item_id = $(this).data('id');
            if(window.axios && _item_id) {
                axios.get(`/admin/qr-code/${_item_id}/get-detail`).then(res => {
                    console.log(res)
                    if(res.status === 200 && res.data) {
                        let _data = res.data.data;
                        const _print_html = `
                            <div class="card p-4" id="print-content">
                                <h1 style="margin-bottom: 30px">${_data.name}</h1>
                                <div style="margin-bottom: 30px; text-align: justify;">${_data.description}</div>
                                <div style="text-align: center">
                                    <img class="mw-100" src="${_data.qr_code}">
                                </div>
                            </div>
                        `
                        var printContents = $(_print_html).html();
                        w = window.open();
                        w.document.write(printContents);
                        w.print();
                        w.close();
                    }
                })
            }
        })
    </script>
@endsection


@section('modals')
    @if( Auth::user()->isSuperAdmin() )
    @include('app.qr-code.modals.add')
    @include('app.qr-code.modals.cancel')
    @endif
@endsection
