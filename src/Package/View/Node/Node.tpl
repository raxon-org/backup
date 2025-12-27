{{$response = Package.Raxon.Backup:Node:node.create(flags(), options())}}
{{$response|>object:'json'}}
