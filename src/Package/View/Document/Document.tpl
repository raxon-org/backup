{{$response = Package.Raxon.Backup:Document:document.create(flags(), options())}}
{{$response|>object:'json'}}
