{{$response = Package.Raxon.Backup:Log:log.create(flags(), options())}}
{{$response|>object:'json'}}
