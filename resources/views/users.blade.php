@extends('base')

@section('title', 'Users')

@section('menu')
    @include('menu')
@endsection

@section('body')
    <div class="row">
        <div class="col-sm-12">
            <div class="table-responsive">
                <table id="users" class="table table-sm table-hover table-striped" style="width:100%"></table>
            </div>
        </div>
    </div>
@endsection

@section('script')
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
            },
            {
                text: "<i class=\"fa fa-plus\" aria-hidden=\"true\"></i>",
                titleAttr: 'ADD USERS',
                className: "btn-default",
                action: function (e, dt, node, config) {
                    location.href = '{{ route('users.load') }}'
                }
            }
        ];
        const columns = [
            {title: "#", data: "id", className: "text-center"},
            {title: "name", data: "name", className: "text-center"},
            {title: "username", data: "username", className: "text-center"},
            {title: "type", data: "type", className: "text-center"},
            {title: "total accounts added", data: "accounts_count", className: "text-center", searchable: false},
            {title: "total processes created", data: "processes_count", className: "text-center", searchable: false},
            {
                title: "action",
                data: null,
                className: "text-center",
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    const edit = `<button data-id="${row.id}" class="btn btn-sm btn-primary edit">EDIT</button>&nbsp;`;
                    const status = `<button data-id="${row.id}" data-action="${!row.is_active ? 1 : 0}" class="btn btn-sm btn-warning status">${row.is_active ? 'DISABLE' : 'ENABLE'}</button>&nbsp;`;
                    const del = `<button data-id="${row.id}" class="btn btn-sm btn-danger del">DELETE</button>&nbsp;`;
                    return edit + status + del;
                }
            },
        ];
        $.fn.dataTable.Buttons.defaults.dom.button.className = 'btn btn-default border';

        const table = $("#users").DataTable({
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
            ajax: ' {{ route('users.index') }}',
            rowCallback: function (row, data) {
                if (!data.is_active) {
                    $(row).addClass("table-danger");
                    return row;
                }
            }
        }).on("click", ".edit", function (e) {
            e.preventDefault();
            const btn = this;
            const id = $(btn).data('id');
            location.href = '{{ route('users.load', ':id') }}'.replace(':id', id)
        }).on("click", ".status", function (e) {
            e.preventDefault();
            const btn = this;
            const id = $(btn).data('id');
            const status = $(btn).data('action');
            $(btn).attr("disabled", true);
            $.ajax({
                url: '{{ route('users.status', ':id') }}'.replace(':id', id),
                type: "patch",
                dataType: "json",
                data: {
                    status
                }
            }).done(function (res) {
                table.ajax.reload();
            }).always(function () {
                $(btn).attr("disabled", false);
            });
        }).on("click", ".del", function (e) {
            e.preventDefault();
            const btn = this;
            const id = $(btn).data('id');
            if(confirm('Are You Sure ?')) {
                $(btn).attr("disabled", true);
                $.ajax({
                    url: '{{ route('users.delete', ':id') }}'.replace(':id', id),
                    type: "delete",
                    dataType: "json",
                }).done(function (res) {
                    if (res.status)
                        table.ajax.reload();
                    else
                        Swal.fire({
                            icon: "error",
                            title: "Oops",
                            text: "Failed"
                        });
                }).always(function () {
                    $(btn).attr("disabled", false);
                });
            }
        });
    </script>
@endsection
