# AOMEI Backupper is corrupting files
Recently when doing some VM/DB maintenance I came to a realization that AOMEI Backupper will corrupt large files when doing directory backups, not sure if other kind of backups/restore are affected. This issue seems to be present in all recent versions, also in the newest one 7.4.2. Tested 7.4.2 and 7.3.3. The issue is so critical that the users could be left with totally unusable backups.

After analyzing the files before/after the restore - it seems that some regions of random files will be empty. Also by analyzying before and after it seems that sometimes data is misplaced (we have data which belongs in some other place, in totally random locations). These errors are repeatable across different systems and different software versions (MD5 hashes of restored files will differ from source, so the file is corrupted - but we'll always be getting exactly the same corruption).

The other issue is that "Integrity verification" feature will only verify the integrity of the backup itself, it won't compare the files stored on drive vs the files in backup. So in all the cases the backup will be integral but the user will be restoring... corrupted data. The last issue is that there's no possibility to view any kind of file checksum. So the user isn't even albe to REALLY verify the integrity of backed up files, without a full restore. The built-in "verification" fearture should be carefully documented that it only verifies the integrity of the backup file, not integrity of the source as in current shape it only gives a false hope of integrity. All corrupted backups i made - successfully passed the integrity tests. Probably all ingested files (at least in dir/file mode) need to have their checksums written and then the checksum needs to be compared to data from archive during verification, for this verification to make sense.

#The issue
After restoring data, eg. full VM Guest Files, they seem working at a first glance, but there are issues such as:
- Constant segfaults of different software
- Lost of full database tables as metadata is getting corrupted
- Inability of databases to fully read tables (lost connection, random errors during full table scans after correctly reading only some part of the table)
- Lost file content

There is an example below for file backup and restore checksums (restore was done right after backup on disabled VM)
```
Name                            MD5                               Size          Date              Cumulative Size  
Debian8-Apache-cl1-000001.vmdk  26a28c6491294ccf843104c18b9830f8  153336086528  06.07.2020 11:34  153336086528      
Debian8-Apache-cl1-000002.vmdk  a834ae2766dea493c0728229435bc1b2  56730517504   23.02.2021 11:01  210066604032      
Debian8-Apache-cl1-000003.vmdk  46769fa67992af7cdf4ebd5c1195177e  66333704192   17.01.2022 23:18  276400308224      
Debian8-Apache-cl1-000004.vmdk  7ff5a7ebc23294c9a088bbec2adae8ba  23677370368   28.09.2022 12:56  300077678592      
Debian8-Apache-cl1-000005.vmdk  172381dfa7d8e713a3b95e04ade379fd  27771273216   28.01.2025 19:25  327848951808      
Debian8-Apache-cl1-000006.vmdk  6caeb207f66770d817df7dc7d4dbd712  1242890240    28.01.2025 22:54  329091842048      
Debian8-Apache-cl1.vmdk         64935940800598f0cc0e6403c8d0e694  66299625472   12.02.2018 19:31  395391467520      
Debian8-Apache-Snapshot10.vmem  f6805a0058b952043ceaf150ca97b97a  8589934592    23.02.2021 11:13  403981402112      
Debian8-Apache-Snapshot10.vmsn  c0f53e4ad04b8c696b1b90ae782aa58a  136109044     23.02.2021 11:13  404117511156      
Debian8-Apache-Snapshot12.vmem  84bc83163e83bcef3ec565d49d57f4fc  8589934592    17.01.2022 23:30  412707445748      
Debian8-Apache-Snapshot12.vmsn  904bb2239ed283a347420c2dd78dfd90  136100770     17.01.2022 23:30  412843546518      
Debian8-Apache-Snapshot13.vmem  8448fcabb5b389c215c62d37215f5a78  8589934592    28.09.2022 13:09  421433481110      
Debian8-Apache-Snapshot13.vmsn  e182312849c1de5f915f82c8de9b86e3  136100334     28.09.2022 13:09  421569581444      
Debian8-Apache-Snapshot14.vmem  b9c2eb1c01a0fdd38396e4bfddfeb961  8589934592    28.01.2025 19:29  430159516036      
Debian8-Apache-Snapshot14.vmsn  5b4f16b3c7ab2fd77916ea981a076f2b  136100334     28.01.2025 19:29  430295616370      
Debian8-Apache-Snapshot7.vmsn   ad16a8d01f4af0de46644e956e6e2bc1                                                    
Debian8-Apache-Snapshot9.vmsn   ab05b7f5412e34994bf857ed548ab697                                                    
Debian8-Apache.nvram            d4a165c8239211f4406525818f02d2c4                                                    
Debian8-Apache.vmsd             0990b2a92ec975e3230712e890cdbd85                                                    
Debian8-Apache.vmx              6a482b833bdbadf31f977221fbf8752a                                                    
Debian8-Apache.vmxf             8908057609af9379af03d4039a82c368                                                    
vm-56.scoreboard                047f00f3e89a47a5c713970f279387e7                                                    
vm-57.scoreboard                0829f71740aab1ab98b33eae21dee122                                                    
vm-58.scoreboard                ab91ac024511d41ba5aaf92bc8a6d220                                                    
vm.scoreboard                   31ad1cf7fd7cfe0cacdaf043e0501b1f                                                    
vmware-0.log                    2c18e290be7d9aaec2ab8926a3f13afe                                                    
vmware-1.log                    b777db724ab9e71dfa6d555ffe8c6fd1                                                    
vmware-2.log                    122c2820c12638ee94ac3c9b5989078c                                                    
vmware.log                      b562b2eda7c9a7a7837e47986859504a 
```

After restore we can see several corrupted files. 
```
Name                            MD5                                 Size          Date              Cumulative Size  
Debian8-Apache-cl1-000001.vmdk  26a28c6491294ccf843104c18b9830f8    153336086528  06.07.2020 12:34  153336086528      
Debian8-Apache-cl1-000002.vmdk  a834ae2766dea493c0728229435bc1b2    56730517504   23.02.2021 11:01  210066604032      
Debian8-Apache-cl1-000003.vmdk  **a7fc6d199fdedebc480f612a53c4483c  66333704192   17.01.2022 23:18  276400308224      
Debian8-Apache-cl1-000004.vmdk  **eed4caace9e041cf5fb0873ff70c0f55  23677370368   28.09.2022 13:56  300077678592      
Debian8-Apache-cl1-000005.vmdk  **fd2617bef446d12ae1e976315ff7aa1c  27771273216   28.01.2025 19:25  327848951808      
Debian8-Apache-cl1-000006.vmdk  6caeb207f66770d817df7dc7d4dbd712    1242890240    28.01.2025 22:54  329091842048      
Debian8-Apache-cl1.vmdk         **278d6e79694586501db28433033b8619  66299625472   12.02.2018 19:31  395391467520      
Debian8-Apache-Snapshot10.vmem  f6805a0058b952043ceaf150ca97b97a    8589934592    23.02.2021 11:13  403981402112      
Debian8-Apache-Snapshot10.vmsn  c0f53e4ad04b8c696b1b90ae782aa58a    136109044     23.02.2021 11:13  404117511156      
Debian8-Apache-Snapshot12.vmem  84bc83163e83bcef3ec565d49d57f4fc    8589934592    17.01.2022 23:30  412707445748      
Debian8-Apache-Snapshot12.vmsn  904bb2239ed283a347420c2dd78dfd90    136100770     17.01.2022 23:30  412843546518      
Debian8-Apache-Snapshot13.vmem  8448fcabb5b389c215c62d37215f5a78    8589934592    28.09.2022 14:09  421433481110      
Debian8-Apache-Snapshot13.vmsn  e182312849c1de5f915f82c8de9b86e3    136100334     28.09.2022 14:09  421569581444      
Debian8-Apache-Snapshot14.vmem  b9c2eb1c01a0fdd38396e4bfddfeb961    8589934592    28.01.2025 19:29  430159516036      
Debian8-Apache-Snapshot14.vmsn  5b4f16b3c7ab2fd77916ea981a076f2b    136100334     28.01.2025 19:29  430295616370      
Debian8-Apache-Snapshot7.vmsn   ad16a8d01f4af0de46644e956e6e2bc1                                                    
Debian8-Apache-Snapshot9.vmsn   ab05b7f5412e34994bf857ed548ab697                                                    
Debian8-Apache.nvram            d4a165c8239211f4406525818f02d2c4                                                    
Debian8-Apache.vmsd             0990b2a92ec975e3230712e890cdbd85                                                    
Debian8-Apache.vmx              6a482b833bdbadf31f977221fbf8752a                                                    
Debian8-Apache.vmxf             8908057609af9379af03d4039a82c368                                                    
vm-56.scoreboard                047f00f3e89a47a5c713970f279387e7                                                    
vm-57.scoreboard                0829f71740aab1ab98b33eae21dee122                                                    
vm-58.scoreboard                ab91ac024511d41ba5aaf92bc8a6d220                                                    
vm.scoreboard                   31ad1cf7fd7cfe0cacdaf043e0501b1f                                                    
vmware-0.log                    2c18e290be7d9aaec2ab8926a3f13afe                                                    
vmware-1.log                    b777db724ab9e71dfa6d555ffe8c6fd1                                                    
vmware-2.log                    122c2820c12638ee94ac3c9b5989078c                                                    
vmware.log                      b562b2eda7c9a7a7837e47986859504a                
```

The corruption will ALWAYS be the same, doesn't matter if the backup is made on AMD or on Intel system. Doesn't matter what AOMEI Backupper version we're using. The resulting set of files is basically runnable, but unusable because all installed apps are crashing, missing data or are unstable, when run. Corruption seems to not be contained inside single file, many times I saw that file B got corrupted, when i put specific file A into the backup. Even more weird was that file A was backed up and restored correctly, it just affected file's B data, so maybe there's some overflow in some variable inside the backup/restore code.

# Reproducing the issue
Please download all attached files and run the command below in CLI.
```
php -f gen_dataset2.php
```

Output dataset checksums:
```
> foreach($f in dir) { if (!$f.PSIsContainer) {certutil -hashfile "$f" md5} }
MD5 hash of Debian8-Apache-cl1-000001.vmdk:
899777421494a3e9ea57b38bfb7ca7db
CertUtil: -hashfile command completed successfully.
MD5 hash of Debian8-Apache-cl1.vmdk:
f9177ae1dfc1d20e6b41a5e95a84f75f
```

Checksum after backup + restore:
```
> foreach($f in dir){ if (!$f.PSIsContainer) {certutil -hashfile "$f" md5} }
MD5 hash of Debian8-Apache-cl1-000001.vmdk:
899777421494a3e9ea57b38bfb7ca7db
CertUtil: -hashfile command completed successfully.
MD5 hash of Debian8-Apache-cl1.vmdk:
****** 688273c35bb1133d08b82b15e51772bf
CertUtil: -hashfile command completed successfully.
```

# Solution
Backup algorithm needs to be fixed. Vefification for directory backup mode needs to be made better, not sure if not disabled / removed as currently it doesn't have any point and doesn't really verify integrity of the backup, at lease in this mode.
