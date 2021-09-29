@extends('base')

@section('title', 'Netlify')

@section('menu')
    @include('menu')
@endsection

@section('body')
    <div class="row">
        <div class="col-sm-12">
            <div class="p-2">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" class="form-control"/>
                </div>
                <div class="form-group">
                    <label for="token">Token:</label>
                    <input type="text" id="token" class="form-control"/>
                </div>
                <button id="add" class="btn btn-primary">ADD ACCOUNT</button>
            </div>
        </div>
    </div>
    <hr>
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
                text: 'reload',
                action: function (e, dt, node, config) {
                    dt.ajax.reload();
                }
            }
        ];
        const columns = [
            {title: "#", data: "id", className: "text-center"},
            {title: "name", data: "name", className: "text-center"},
            {title: "token", data: "token", className: "text-center"},
            {
                title: "action",
                data: null,
                className: "text-center",
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    //const check = `<button data-id="${row.id}" class="btn btn-sm btn-info check">CHECK API LIMIT</button>&nbsp;`;
                    const status = `<button data-id="${row.id}" data-action="${!row.is_active ? 1 : 0}" class="btn btn-sm btn-warning status">${row.is_active ? 'DISABLE' : 'ENABLE'}</button>&nbsp;`;
                    const del = `<button data-id="${row.id}" class="btn btn-sm btn-danger del">DELETE</button>&nbsp;`;
                    return  status + del;
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
        }).on("click", ".check", function (e) {
            e.preventDefault();
            const btn = this;
            const id = $(btn).data('id');
            $(btn).attr("disabled", true);
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
                $(btn).attr("disabled", false);
            });
        }).on("click", ".status", function (e) {
            e.preventDefault();
            const btn = this;
            const id = $(btn).data('id');
            const status = $(btn).data('action');
            $(btn).attr("disabled", true);
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
                $(btn).attr("disabled", false);
            });
        }).on("click", ".del", function (e) {
            e.preventDefault();
            const btn = this;
            const id = $(btn).data('id');
            if (confirm('Are You Sure ?')) {
                $(btn).attr("disabled", true);
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
                    $(btn).attr("disabled", false);
                });
            }
        });


        $("#add").click(function (e) {
            e.preventDefault();
            const name = $("#name").val();
            const token = $("#token").val();

            if (name && token) {
                const btn = this;
                $(btn).attr('disabled', true).html("ADDING " + spinner);
                $.ajax({
                    url: '{{ route('accounts.add') }}',
                    type: "post",
                    dataType: "json",
                    data: {
                        name,
                        token
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
