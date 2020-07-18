/*
chown("___cache", "philippe.miossec");
chown("__cache", "philippe.miossec");
chown("_cache", "philippe.miossec");
chmod("___cache", 0777);
chmod("__cache", 0777);
chmod("_cache", 0777);
echo substr(sprintf('%o', fileperms('__cache')), -4) . "<br/>";
echo substr(sprintf('%o', fileperms('_cache')), -4) . "<br/>";
rmdir("___cache");
rmdir("__cache");
rmdir("_cache");
return;

/*echo substr(sprintf('%o', fileperms('__cache')), -4) . "<br/>";
echo substr(sprintf('%o', fileperms('_cache')), -4) . "<br/>";
chmod("__cache", 0777);
chmod("_cache", 0777);
echo substr(sprintf('%o', fileperms('__cache')), -4) . "<br/>";
echo substr(sprintf('%o', fileperms('_cache')), -4) . "<br/>";
rmdir("__cache");
rmdir("_cache");
return;
*/