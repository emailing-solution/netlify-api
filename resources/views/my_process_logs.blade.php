@extends('base')

@section('title', 'My Process Logs')

@section('menu')
    @include('menu')
@endsection

@section('body')
    <div class="row">
        <div class="col-sm-12">
            <div class="table-responsive">
                <table id="logs" class="table table-sm table-hover table-striped" style="width:100%"></table>
            </div>
        </div>
    </div>
@endsection


@section('script')
    <script>
        const jsonToHtml = function (json) {
            let list =  '<ul>';
            $.each(json, function(key, value) {
                list+= `<li><strong>${key}: </strong>${JSON.stringify(value)}</li>`;
            });
            list += '</ul>';
            return list;
        }

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
            {
                title: "headers", data: "headers", className: "text-center", render: function (data) {
                    const dataDecoded = $("<div/>").html(data).text();
                    const headers = JSON.parse(dataDecoded);
                    console.log(headers);
                    return jsonToHtml(headers);
                }
            },
            {
                title: "body", data: "body", className: "text-center", render: function (data) {
                    const dataDecoded = $("<div/>").html(data).text();
                    const headers = JSON.parse(dataDecoded);
                    return jsonToHtml(headers);
                }
            },
            {title: "total limit", data: "total_limit", className: "text-center"},
            {title: "total left", data: "total_left", className: "text-center"},
            {title: "retry at", data: "retry_at", className: "text-center"},
        ];

        $.fn.dataTable.Buttons.defaults.dom.button.className = 'btn btn-default border';
        const table = $("#logs").DataTable({
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
            ajax: ' {{ url()->current() }}',
        })
    </script>
@endsection
