{{$register = Package.Raxon.Backup:Init:register()}}
{{if(!is.empty($register))}}
{{Package.Raxon.Backup:Import:role.system()}}
{{Package.Raxon.Backup:Setup:run()}}
{{/if}}