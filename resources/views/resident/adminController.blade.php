<div class="admin_control_bar">
    
    <div class="switch_building">
        <div class="dropdown">
            <button class="btn btn-sm btn-purple btn-round btn-arrow dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                {{$current_building ? $current_building->name : 'Switch Building'}} <i class="material-icons">arrow_upward</i>
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            @if($buildings)
                @foreach($buildings as $building)
                    <li>
                        <a href="{{route('app.switch.building', $building->id)}}" class="align-middle"> 
                            @if($building->getThumb())
                                <span class="initials _bg" style="background-image: url({{$building->getThumb()}})"></span>
                            @endif
                            <span class="align-middle">{{$building->name}}</span>
                        </a>
                    </li>
                @endforeach
            @endif
            </div>
        </div>
    </div>

    <div class="back_to_admin">
        <a href="/back-to-admin" class="btn btn-sm ml-3 btn-round btn-purple btn-arrow">Back to admin <i class="material-icons">arrow_forward</i></a>
    </div>

</div>