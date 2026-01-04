{{$response = Package.Raxon.Backup:Photo:photo.create(flags(), options())}}
{{$response|>object:'json'}}
