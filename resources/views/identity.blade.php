@extends('base')

@section('title', 'Invite Users')

@section('menu')
    @include('menu')
@endsection

@section('css')
    <link rel="stylesheet" type="text/css"
          href="//cdn.datatables.net/v/bs4/dt-1.10.20/b-1.6.5/r-2.2.6/sl-1.3.1/datatables.min.css"/>
@endsection

@section('body')
    <div class="row">
        <div class="col-sm-12">
            <div class="form-group">
                <label for="subject">Subject:</label>
                <input type="text" id="subject" class="form-control"/>
            </div>
            <div class="form-group">
                <label for="template">Template:</label>
                <input type="text" id="template" class="form-control"/>
            </div>
            <button id="change" class="btn btn-primary">CHANGE TEMPLATE</button>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-sm-12">
            <div class="table-responsive">
                <table id="identity" class="table table-sm table-hover table-striped" style="width:100%">
                </table>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="//cdn.datatables.net/v/bs4/dt-1.10.20/b-1.6.5/r-2.2.6/sl-1.3.1/datatables.min.js"></script>
    <script>
        const buttons = [
            {
                extend: "pageLength",
                className: ""
            },
            {
                text: "<i class=\"fa fa-check-square-o\" aria-hidden=\"true\"></i>",
                titleAttr: 'SELECT ALL',
                className: "btn-default",
                action: function (e, dt, node, config) {
                    dt.rows({page: 'current'}).select();
                }
            },
            {
                text: "<i class=\"fa fa-square-o\" aria-hidden=\"true\"></i>",
                titleAttr: 'SELECT NONE',
                className: "btn-default",
                action: function (e, dt, node, config) {
                    dt.rows({page: 'current'}).deselect();
                }
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
                    // add accounts

                    Swal.fire({
                        input: 'textarea',
                        inputLabel: 'Emails',
                        inputPlaceholder: 'Put Your Emails Here',
                        preConfirm: function (data) {
                            return new Promise(function (resolve, reject) {
                                const emails = data.split("\n").filter(e => e.trim())
                                console.log(emails);
                                $.ajax({
                                    url: '{{ url()->current() }}',
                                    method: "post",
                                    dataType: "json",
                                    data: {
                                        action: 'add',
                                        emails
                                    }
                                }).done(function (res) {
                                    resolve(res)
                                });
                            })
                        },
                        allowOutsideClick: () => !Swal.isLoading(),
                        showLoaderOnConfirm: true,
                    }).then((res) => {
                        Swal.fire({
                            icon: "success",
                            title: "done",
                            text: res.data
                        }).then(() => dt.ajax.reload(null, false));
                    })
                }
            },
            {
                text: "<i class=\"fa fa-times\" aria-hidden=\"true\"></i>",
                titleAttr: 'REMOVE EMAILS',
                className: "btn-default",
                action: function (e, dt, node, config) {
                    const data = dt.rows({selected: true}).data().toArray();
                    if (data.length) {
                        Swal.fire({
                            icon: 'question',
                            title: 'Are You Sure ?',
                            text: 'All Selected Emails Will Be Deleted',
                            allowOutsideClick: false,
                            showCloseButton: true,
                            confirmButtonText: 'Yes, delete it!',
                            showCancelButton: true,
                        }).then((result) => {
                            if (result.value) {
                                dt.buttons(node).disable();
                                $.ajax({
                                    url: '{{ url()->current() }}',
                                    method: "post",
                                    dataType: "json",
                                    data: {
                                        action: 'remove',
                                        users: data.map(v => v.id)
                                    }
                                }).done(function (res, statusText, xhr) {
                                    Swal.fire({
                                        icon: "success",
                                        title: "done",
                                        text: res.data
                                    }).then(() => dt.ajax.reload(null, false));
                                }).always(function () {
                                    dt.buttons(node).enable();
                                });
                            }
                        })
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Nothing Selected",
                        });
                    }
                }
            },
        ];
        const columns = [
            {targets: 0, data: null, defaultContent: '', orderable: false, className: 'select-checkbox'},
            {title: "id", data: "id", className: "text-center"},
            {title: "email", data: "email", className: "text-center"},
            {title: "invited_at", data: "invited_at", className: "text-center"},
            {title: "updated_at", data: "updated_at", className: "text-center"},
        ];

        $.fn.dataTable.Buttons.defaults.dom.button.className = 'btn btn-default border';
        const table = $("#identity").DataTable({
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
            processing: true,
            ajax: '{{ url()->current() }}' + '?t=u',
            select: {
                style: 'multi',
                selector: 'td:first-child'
            },
        });

        $(function () {
            $.ajax({
                url: '{{ url()->current() }}' + '?t=i',
                type: "get",
                dataType: "json",
            }).done(function (res) {
                $("#subject").val(res.config.config.mailer.subjects.invite)
                $("#template").val(res.config.config.mailer.templates.invite)
            })
        });

        $("#change").click(function (e) {
            e.preventDefault();
            const subject = $("#subject").val();
            const template = $("#template").val();

            if (subject && template) {
                const btn = this;
                $(btn).attr('disabled', true).html("CHANGING " + spinner);
                $.ajax({
                    url: '{{ url()->current() }}',
                    type: "post",
                    dataType: "json",
                    data: {
                        action: 'template',
                        subject,
                        template
                    }
                }).done(function (res) {
                    Swal.fire({
                        icon: "success",
                        title: "Done",
                        text: res.data
                    })
                }).always(function () {
                    $(btn).attr("disabled", false).html("CHANGE TEMPLATE");
                });
            }
        });
    </script>
@endsection
