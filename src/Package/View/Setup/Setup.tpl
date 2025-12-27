{{$register = Package.Raxon.Backup:Init:register()}}
{{if(!is.empty($register))}}
{{Package.Raxon.Backup:Import:role.system()}}
{{Package.Raxon.Backup:Setup:backup.install()}}
{{Package.Raxon.Backup:Main:sync(flags(), options())}}
{{/if}}