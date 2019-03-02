# How to make the library available to the public:

1 - Make sure all tests pass

2 - Commit and push all the new version changes to repository.

3 - Review git changelog to decide the new version value based on the GIT changes: minor, major, ...

4 - Make sure the git tag is updated with the new project version we want to publish
    (First in remote GIT repo and then in our Local by performing a fetch)
    
5 - Update the version number on the project root package.json file
    Make sure we have increased the version number regarding the previously published one

6 - Generate a release build executing tests (tb -crt)

7 - Copy the package.json file from the project root to target/turbodepot-node-x.x.x/dist/ts

8 - Add the readme.md file if exists to the target/turbodepot-node-x.x.x/dist/ts folder

9 - Open a command line inside target/turbodepot-node-x.x.x/dist/ts folder and run:
    npm publish
   
10 - Verify that new version appears for the package at www.npmjs.com/~edertone 
   
11 - Get the downloadable zip file and update the files inside with the new versions
   - docs, readme, compiled code, etc..  

12 - Upload the new zip version to turbodepot website for direct download
    - review that zip download works as expected

13 - Upload the new generated docs to the turbodepot website
    - review that links to docs still work
