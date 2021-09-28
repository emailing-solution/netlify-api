@extends('base')

@section('title', 'Sites')

@section('menu')
    @include('menu')
@endsection

@section('css')
    <link rel="stylesheet" type="text/css"
          href="//cdn.datatables.net/v/bs4/dt-1.10.20/b-1.6.5/r-2.2.6/sl-1.3.1/datatables.min.css"/>
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/css/bootstrap-select.min.css">
@endsection

@section('body')
    <div class="row">
        <div class="col-sm-12">
            <div class="p-2">
                <div class="form-group">
                    <label for="accounts">Netlify:</label>
                    <select id="accounts" class="form-control selectpicker" data-live-search="true"
                            data-actions-box="true">
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button id="load" class="btn btn-primary">LOAD SITES</button>
            </div>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-sm-12">
            <div class="table-responsive">
                <table id="sites" class="table table-sm table-hover table-striped" style="width:100%">
                </table>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="//cdn.datatables.net/v/bs4/dt-1.10.20/b-1.6.5/r-2.2.6/sl-1.3.1/datatables.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/bootstrap-select.min.js"></script>
    <script>

        const load_sites = (data) => {
            const buttons = [
                {
                    extend: "pageLength",
                    className: ""
                }
            ];
            const columns = [
                {title: "#", data: "id", className: "text-center"},
                {title: "name", data: "name", className: "text-center"},
                {title: "identity", data: "identity_instance_id", className: "text-center"},
                {
                    title: "action",
                    data: null,
                    className: "text-center",
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        const visit_url = "{{ route('sites.identity', [':acc', ':id', ':iden']) }}"
                            .replace(':acc', $("#accounts").val())
                            .replace(':id', data.id)
                            .replace(':iden', data.identity_instance_id)

                        const process_link = "{{ route('process.get', [':acc', ':id', ':iden']) }}"
                            .replace(':acc', $("#accounts").val())
                            .replace(':id', data.id)
                            .replace(':iden', data.identity_instance_id)

                        const process = `<a href="${process_link}" title="create process" target="_blank">CREATE PROCESS</a> | `;
                        const url = `<a href="${row.ssl_url}" title="visit link" target="_blank">VISIT SITE</a> | `;
                        const visit = `<a href="${visit_url}" title="invite users" target="_blank">MANAGE IDENTITY</a>`

                        return process + url + visit;
                    }
                },
            ];

            $.fn.dataTable.Buttons.defaults.dom.button.className = 'btn btn-default border';
            if ($.fn.DataTable.isDataTable('#lists')) {
                $('#sites').DataTable().clear().destroy();
            }
            const table = $("#sites").DataTable({
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
                data: data,
            })
        };

        $("#load").click(function (e) {
            e.preventDefault();
            const id = $("#accounts").val();
            if (id) {
                const btn = this;
                $(btn).attr('disabled', true).html("LOADING " + spinner);
                $.ajax({
                    url: '{{ route('sites.load', ':id') }}'.replace(':id', id),
                    type: "get",
                    dataType: "json",
                }).done(function (res) {
                    load_sites(res)
                }).always(() => $(btn).html("LOAD SITE").attr("disabled", false));
            }
        });
    </script>
@endsection
