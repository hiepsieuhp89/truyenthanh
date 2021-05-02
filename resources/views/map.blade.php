<?xml version="1.0" encoding="ISO-8859-1"?>
<markers>
	@foreach($devices as $device)
		<marker id="{{$device->id}}" name="{{$device->name}}" address="{{$device->address}}" lat="{{$device->lat}}" lng="{{$device->lon}}" type="{{$device->status}}">
		</marker>
	@endforeach
</markers>