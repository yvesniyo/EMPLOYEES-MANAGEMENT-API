<div>
    <h4>Hi, {{ $manager->name }}</h4>
    <p>
        You requested to reset password, click here to visit reset page <a href="{{$link}}">link</a>. if link is not clickable use this url {{$link}} </p>
    <p>
    <p>Note that this link will expire in {{$manager->reset_code_expires_in->format("Y-m-d h:i:s A")}}</p>

        <br />
        If you did not request this,please change your password, cause someone might want to hack you.
    </p>

</div>