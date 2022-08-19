URL: {{$url}}  
<br/>
@if($user)
    User: {{$user->name}} [{{$user->id}}]
    <br/>
    User Role: {{$user->roles()->first()->name}}   
    <br/>  
@else
    User: Anonymous User or Cron Task
@endif

<br/>
<br/>

```
{!! $error !!}
```

