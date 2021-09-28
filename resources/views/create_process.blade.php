@extends('base')

@section('title', 'Create Process')

@section('menu')
    @include('menu')
@endsection


@section('body')
    <div class="row">
        <div class="col-sm-12">
            <div class="p-2">
                <div class="form-group">
                    <label for="emails">Emails:</label>
                    <textarea id="emails" class="form-control" rows="10"></textarea>
                </div>
                <div class="form-group">
                    <label for="split">Split by:</label>
                    <input type="number" max="30" min="1" id="split" class="form-control" value="30" />
                </div>
                <div class="form-group">
                    <label for="delay">Delay by (seconds):</label>
                    <input type="number" id="delay" class="form-control" min="0" value="0" />
                </div>
                <button id="create" class="btn btn-primary">CREATE PROCESS</button>
            </div>
        </div>
    </div>
@endsection


@section('script')
    <script>
        $("#create").on('click', function (e) {
            e.preventDefault();

            const emails = $("#emails").val().split("\n").filter((e) => e.trim());
            const split = $("#split").val();
            const delay = $("#delay").val();

            if(emails.length > 0 ) {
                $.ajax({
                    url: '{{ url()->current() }}',
                    type: "post",
                    dataType: "json",
                    data: {
                        emails,
                        split,
                        delay
                    }
                }).done(function (res) {
                    Swal.fire({
                        icon: "success",
                        title: "Done",
                        text: res.data
                    }).then(() => {
                        location.href = '{{ route('process.index') }}'
                    });
                }).always(() => $(btn).html("CREATE PROCESS").attr("disabled", false));
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Put Emails",
                });
            }

        })
    </script>
@endsection
