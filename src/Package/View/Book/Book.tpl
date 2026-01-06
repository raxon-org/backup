{{$response = Package.Raxon.Backup:Book:book.create(flags(), options())}}
{{$response|>object:'json'}}
