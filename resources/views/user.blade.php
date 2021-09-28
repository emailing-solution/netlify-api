@extends('base')

@section('title', 'User')

@section('menu')
    @include('menu')
@endsection

@section('body')
    <div class="row">
        <div class="col-sm-12">
            <div class="p-2">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" class="form-control" value="{{ $user->name ?? '' }}"/>
                </div>
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" class="form-control" value="{{ $user->username ?? '' }}"/>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="text" id="password" class="form-control"/>
                </div>
                <div class="form-group">
                    <label for="type">Type:</label>
                    <select id="type" class="form-control">
                        <option value="admin">Admin</option>
                        <option value="mailer" selected>Mailer</option>
                    </select>
                </div>
                <button id="action" class="btn btn-primary">{{ empty($user) ? 'ADD' : 'EDIT' }} USER</button>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        @isset($user->type)
            $("#type").val('{{ $user->type }}')
        @endisset
        $("#action").on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const name = $("#name").val();
            const username = $("#username").val();
            const password = $("#password").val();
            const type = $("#type").val();


            const btn = this;
            $(btn).attr('disabled', true).html("{{ empty($user) ? 'ADDING' : 'EDITING' }} " + spinner);
            $.ajax({
                url: '{{ isset($user->id) ? route('users.add', $user->id) : route('users.add') }}',
                type: "post",
                dataType: "json",
                data: {
                    name,
                    username,
                    password,
                    type,
                }
            }).done(function (res) {
                Swal.fire({
                    icon: "success",
                    title: "Done",
                    text: res.data
                }).then(() => {
                    location.href = "{{ route('users.index') }}"
                });
            }).always(() => $(btn).html("{{ empty($user) ? 'ADD' : 'EDIT' }} ACCOUNT").attr("disabled", false));
        });
    </script>
@endsection
