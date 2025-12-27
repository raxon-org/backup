{{$request = request()}}
Backing up node: {{config('volume.dir.node')}}

{{$response = Package.Raxon.Backup:Node:node.create(flags(), options())}}
{{$response|>object:'json'}}
