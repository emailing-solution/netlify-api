@extends('base')

@section('title', 'Account')

@section('menu')
    @include('menu')
@endsection

@section('body')
    <div class="row">
        <div class="col-sm-12">
            <div class="p-2">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" class="form-control" value="{{ $account->name ?? '' }}"/>
                </div>
                <div class="form-group">
                    <label for="token">Token:</label>
                    <input type="text" id="token" class="form-control" value="{{ $account->token ?? '' }}"/>
                </div>
                <div class="form-group">
                    <label for="proxy">Proxy:port</label>
                    <input type="text" id="proxy" class="form-control" value="{{ $account->proxy ?? '' }}"/>
                </div>
                <button id="action" class="btn btn-primary">{{ empty($account) ? 'ADD' : 'EDIT' }} ACCOUNT</button>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $("#action").on('click', function (e) {
            e.preventDefault();
            const name = $("#name").val();
            const token = $("#token").val();
            const proxy = $("#proxy").val();

            const btn = this;
            $(btn).attr('disabled', true).html("{{ empty($account) ? 'ADDING' : 'EDITING' }} " + spinner);
            $.ajax({
                url: '{{ isset($user->id) ? route('accounts.add', $account->id) : route('accounts.add') }}',
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
                    text: res.data
                }).then(() => {
                    location.href = "{{ route('accounts.index') }}"
                });
            }).always(() => $(btn).html("{{ empty($account) ? 'ADD' : 'EDIT' }} ACCOUNT").attr("disabled", false));
        });
    </script>
@endsection

