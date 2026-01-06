{{$register = Package.Raxon.Backup:Init:register()}}
{{if(!is.empty($register))}}
{{Package.Raxon.Backup:Import:role.system()}}
{{Package.Raxon.Backup:Setup:install()}}
{{$list = dir.read(config('project.dir.data') + 'Cron')}}
{{if(is.array($list))}}
{{foreach($list as $file)}}
{{$read = file.read($file.url)}}
{{d($read)}}
{{/foreach}}
{{/if}}
{{dd($list)}}
0 0 0 * 5 root /usr/bin/app raxon/backup all -exclude[]=Audio -exclude[]=Video -exclude[]=Book
{{/if}}