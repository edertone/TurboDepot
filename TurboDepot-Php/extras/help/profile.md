# How to profile the project


## Analyze PHP performance with xdebug profiler

- Every time the project is built with php, cachegrind.out.xxx files are written inside target\logs\docker\ folder

- You can load these files with the application Wincachegrind (free to download) and analyze the profiling of the executed code.

- If you need to profile only a specific part of code, you can use the "filter" parameter on turbobuilder.json setup file to run only the tests you need so the profiling will be bounded to the executed code.