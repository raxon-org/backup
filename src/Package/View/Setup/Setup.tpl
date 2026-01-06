{{$register = Package.Raxon.Backup:Init:register()}}
{{if(!is.empty($register))}}
{{Package.Raxon.Backup:Import:role.system()}}
{{Package.Raxon.Backup:Setup:install()}}
{{$list = dir.read(config('project.dir.data') + 'Cron')}}
{{if(is.array($list))}}
{{foreach($list as $file)}}
{{$read = explode("\n", file.read($file.url))}}
{{$is.found = false}}
{{foreach($read as $line)}}
{{if(string.position.first.occurrence.case.insensitive($line, 'raxon/backup all'))}}
{{$is.found = true}}
{{break()}}
{{/if}}
{{/foreach}}
{{if($is.found === false)}}
{{$read[] = '0 0 0 * 5 root /usr/bin/app raxon/backup all -exclude[]=Audio -exclude[]=Video -exclude[]=Book'}}
{{file.write($file.url, implode("\n", $read))}}
{{/if}}
{{/foreach}}
{{/if}}
{{$command = binary() + 'raxon/basic cron restore'}}
{{execute($command)}}
{{/if}}