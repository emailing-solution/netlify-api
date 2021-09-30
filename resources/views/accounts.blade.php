@extends('base')

@section('title', 'Netlify')

@section('menu')
    @include('menu')
@endsection

@section('body')
    <div class="row">
        <div class="col-sm-12">
            <div class="table-responsive">
                <table id="accounts" class="table table-sm table-hover table-striped" style="width:100%"></table>
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
                titleAttr: 'ADD ACCOUNT',
                className: "btn-default",
                action: function (e, dt, node, config) {
                    location.href = '{{ route('accounts.load') }}'
                }
            }
        ];
        const columns = [
            {title: "#", data: "id", className: "text-center"},
            {title: "name", data: "name", className: "text-center"},
            {title: "token", data: "token", className: "text-center"},
            {title: "proxy", data: "proxy", className: "text-center"},
            {
                title: "action",
                data: null,
                className: "text-center",
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    let proxy = '';
                    if (row.proxy) {
                        proxy = `<button data-id="${row.id}" class="btn btn-sm btn-primary proxy">CHECK PROXY</button>&nbsp;`;
                    }
                    const edit = `<button data-id="${row.id}" class="btn btn-sm btn-success edit"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>&nbsp;`;
                    const check = `<button data-id="${row.id}" class="btn btn-sm btn-primary check">CHECK API LIMIT</button>&nbsp;`;
                    const status = `<button data-id="${row.id}" data-action="${!row.is_active ? 1 : 0}" class="btn btn-sm btn-${row.is_active ? 'warning' : 'dark'} status">${row.is_active ? '<i class="fa fa-times" aria-hidden="true"></i>' : '<i class="fa fa-check" aria-hidden="true"></i>'}</button>&nbsp;`;
                    const del = `<button data-id="${row.id}" class="btn btn-sm btn-danger del"><i class="fa fa-trash" aria-hidden="true"></i></button>&nbsp;`;
                    return proxy + check + status + edit + del;
                }
            },
        ];
        $.fn.dataTable.Buttons.defaults.dom.button.className = 'btn btn-default border';

        const table = $("#accounts").DataTable({
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
            ajax: ' {{ route('accounts.index') }}',
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
            location.href = '{{ route('accounts.load', ':id') }}'.replace(':id', id)
        }).on("click", ".proxy", function (e) {
            e.preventDefault();
            const btn = this;
            const id = $(btn).data('id');
            const text = $(btn).text();
            $(btn).attr("disabled", true).html(spinner);
            $.ajax({
                url: '{{ route('accounts.proxy', ':id') }}'.replace(':id', id),
                type: "get",
                dataType: "json",
            }).done(function (res) {
                Swal.fire({
                    icon: res.status,
                    title: res.status,
                    text: res.body,
                });
            }).always(function () {
                $(btn).attr("disabled", false).html(text);
            });
        }).on("click", ".check", function (e) {
            e.preventDefault();
            const btn = this;
            const id = $(btn).data('id');
            const text = $(btn).text();
            $(btn).attr("disabled", true).html(spinner);
            $.ajax({
                url: '{{ route('accounts.check', ':id') }}'.replace(':id', id),
                type: "get",
                dataType: "json",
            }).done(function (res) {
                Swal.fire({
                    icon: res.status,
                    title: res.status,
                    text: res.body,
                });
            }).always(function () {
                $(btn).attr("disabled", false).html(text);
            });
        }).on("click", ".status", function (e) {
            e.preventDefault();
            const btn = this;
            const id = $(btn).data('id');
            const status = $(btn).data('action');
            const text = $(btn).text();
            $(btn).attr("disabled", true).html(spinner);
            $.ajax({
                url: '{{ route('accounts.status', ':id') }}'.replace(':id', id),
                type: "patch",
                dataType: "json",
                data: {
                    status
                }
            }).done(function (res) {
                table.ajax.reload();
            }).always(function () {
                $(btn).attr("disabled", false).html(text);
            });
        }).on("click", ".del", function (e) {
            e.preventDefault();
            const btn = this;
            const id = $(btn).data('id');
            if (confirm('Are You Sure ?')) {
                const text = $(btn).text();
                $(btn).attr("disabled", true).html(spinner);
                $.ajax({
                    url: '{{ route('accounts.delete', ':id') }}'.replace(':id', id),
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
                    $(btn).attr("disabled", false).html(text);
                });
            }
        });


        $("#add").click(function (e) {
            e.preventDefault();
            const name = $("#name").val();
            const token = $("#token").val();
            const proxy = $("#proxy").val();

            if (name && token) {
                const btn = this;
                $(btn).attr('disabled', true).html("ADDING " + spinner);
                $.ajax({
                    url: '{{ route('accounts.add') }}',
                    type: "post",
                    dataType: "json",
                    data: {
                        name,
                        token,
                        proxy
                    }
                }).done(function (res) {
                    Swal.fire({
                        icon: "success",
                        title: "Done",
                        text: "Added"
                    }).then(() => {
                        table.ajax.reload();
                    });
                }).always(() => $(btn).html("ADD ACCOUNT").attr("disabled", false));
            }
        });
    </script>
@endsection
