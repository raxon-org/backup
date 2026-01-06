{{$request = request()}}
backups:
{{binary()}} {{$request.package|>default:''}} audio
{{binary()}} {{$request.package|>default:''}} book
{{binary()}} {{$request.package|>default:''}} data
{{binary()}} {{$request.package|>default:''}} desktop
{{binary()}} {{$request.package|>default:''}} document
{{binary()}} {{$request.package|>default:''}} domain
{{binary()}} {{$request.package|>default:''}} log
{{binary()}} {{$request.package|>default:''}} node
{{binary()}} {{$request.package|>default:''}} package
{{binary()}} {{$request.package|>default:''}} photo
{{binary()}} {{$request.package|>default:''}} shared
{{binary()}} {{$request.package|>default:''}} video
restores:
{{binary()}} {{$request.package|>default:''}} restore audio
{{binary()}} {{$request.package|>default:''}} restore book
{{binary()}} {{$request.package|>default:''}} restore data
{{binary()}} {{$request.package|>default:''}} restore desktop
{{binary()}} {{$request.package|>default:''}} restore document
{{binary()}} {{$request.package|>default:''}} restore domain
{{binary()}} {{$request.package|>default:''}} restore log
{{binary()}} {{$request.package|>default:''}} restore node
{{binary()}} {{$request.package|>default:''}} restore package
{{binary()}} {{$request.package|>default:''}} restore photo
{{binary()}} {{$request.package|>default:''}} restore shared
{{binary()}} {{$request.package|>default:''}} restore video

When requested option -date needs to be added in form YYYYMMDD.
Example:
{{binary()}} {{$request.package|>default:''}} restore domain -date={{date('Y-m-d')}}

{{binary()}} {{$request.package|>default:''}} setup
