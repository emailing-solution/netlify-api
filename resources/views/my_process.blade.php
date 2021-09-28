@extends('base')

@section('title', 'My Process')

@section('menu')
    @include('menu')
@endsection

@section('css')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.20/b-1.6.5/r-2.2.6/sl-1.3.1/datatables.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/css/bootstrap-select.min.css">
@endsection


@section('body')
    <div class="row">
        <div class="col-sm-12">
            <div class="table-responsive">
                <table id="myprocess" class="table table-sm table-hover table-striped" style="width:100%"></table>
            </div>
        </div>
    </div>
@endsection


@section('script')
    <script type="text/javascript"
            src="https://cdn.datatables.net/v/bs4/dt-1.10.20/b-1.6.5/r-2.2.6/sl-1.3.1/datatables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/bootstrap-select.min.js"></script>
    <script>
        const buttons = [
            {
                extend: "pageLength",
                className: ""
            },
            {
                text: '<i class="fa fa-refresh" aria-hidden="true"></i>',
                titleAttr: 'RELOAD',
                action: function (e, dt, node, config) {
                    dt.ajax.reload();
                }
            }
        ];
        const columns = [
            {title: "#", data: "id", className: "text-center"},
            {title: "user", data: "user.username", searchable: false, className: "text-center"},
            {title: "split by", data: "split_by", className: "text-center"},
            {title: "delay by", data: "delay_by", className: "text-center"},
            {title: "total sent", data: "total_sent", className: "text-center"},
            {title: "total emails", data: "total_emails", className: "text-center"},
            {title: "status", data: "status", className: "text-center"},
            {title: "pid", data: "pid", className: "text-center"},
            {
                title: "action",
                data: null,
                className: "text-center",
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    if (row.status === 'running')
                        return `<button data-id="${row.id}" class="btn btn-sm btn-danger kill">KILL</button>&nbsp;`;
                    return '';
                }
            },
        ];
        $.fn.dataTable.Buttons.defaults.dom.button.className = 'btn btn-default border';

        const table = $("#myprocess").DataTable({
            dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: buttons,
            columns: columns,
            scrollCollapse: true,
            Destroy: true,
            responsive: true,
            paging: true,
            lengthMenu: [[50, 100, -1], [50, 100, "All"]],
            displayLength: 50,
            order: [[0, "desc"]],
            processing: true,
            serverSide: true,
            ajax: ' {{ route('process.index') }}',
            rowCallback: function (row, data) {
                if (data.status === "finish") {
                    $(row).addClass("table-success");
                    return row;
                }
                if (data.status === "error") {
                    $(row).addClass("table-danger");
                    return row;
                }
            }
        }).on("click", ".kill", function (e) {
            e.preventDefault();
            const btn = this;
            const id = $(btn).data('id');
            if(confirm('Are You Sure ?')) {
                $(btn).attr("disabled", true);
                $.ajax({
                    url: '{{ route('process.kill', ':id') }}'.replace(':id', id),
                    type: "delete",
                    dataType: "json",
                }).done(function (res) {
                    table.ajax.reload();
                }).always(function () {
                    $(btn).attr("disabled", false);
                });
            }
        });
    </script>
@endsection
