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
            let _options = {
                container: 'div',
                formatContainer: function formatContainer(container) {
                    container.className = 'container'
                    return container
                }
                ,
                formatUl: function formatUl(ul) {
                    ul.className = ''
                    return ul
                },
                formatLi: function formatLi(li) {
                    li.className = ''
                    return li
                },
                formatProperty: function formatProperty(val) {
                    const strong = document.createElement('strong')
                    strong.appendChild(val)
                    return strong
                },
                formatValue: function formatValue(val) {
                    let span = document.createElement('span');
                    span.appendChild(val);
                    return span;
                }
            }
            let container = document.createElement(_options.container)
            container = _options.formatContainer(container)

            function walk(obj, parentElm) {
                if (typeof (obj) === 'object' && obj !== null && obj.constructor === Object) {
                    let ul = document.createElement('ul')
                    ul = _options.formatUl(ul)
                    // eslint-disable-next-line no-unused-vars
                    let hasCount = 0
                    for (let prop in obj) {
                        // eslint-disable-next-line no-prototype-builtins
                        if (obj.hasOwnProperty(prop)) {
                            let li = document.createElement('li')
                            li = _options.formatLi(li)
                            ul.appendChild(li)
                            if (typeof (obj[prop]) !== 'object' || obj[prop] === null) {
                                let propText = document.createTextNode(prop + ' :')
                                propText = _options.formatProperty(propText)
                                li.appendChild(propText)
                                let valueText = document.createTextNode(' ' + obj[prop])
                                valueText = _options.formatValue(valueText, prop)
                                li.appendChild(valueText)
                                hasCount++
                            } else {
                                let propText = document.createTextNode(prop)
                                propText = _options.formatProperty(propText)
                                li.appendChild(propText)
                                walk(obj[prop], li)
                            }
                        }
                    }
                    parentElm.appendChild(ul)
                } else if (typeof (obj) === 'object' && obj !== null && obj.constructor === Array) {
                    let ul = document.createElement('ul')
                    ul = _options.formatUl(ul)
                    // eslint-disable-next-line no-unused-vars
                    let hasCount = 0
                    for (let i = 0; i < obj.length; i++) {
                        if (typeof (obj[i]) !== 'object' || obj[i] === null) {
                            let li = document.createElement('li')
                            li = _options.formatLi(li)
                            ul.appendChild(li)
                            let valueText = document.createTextNode(obj[i])
                            valueText = _options.formatValue(valueText, i)
                            li.appendChild(valueText)
                            hasCount++
                        } else {
                            walk(obj[i], parentElm)
                        }
                    }
                    parentElm.appendChild(ul)
                }
            }

            walk(json, container)
            return container.innerHTML
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
                    const headers = JSON.parse(data);
                    return jsonToHtml(headers);
                }
            },
            {
                title: "body", data: "body", className: "text-center", render: function (data) {
                    const headers = JSON.parse(data);
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
