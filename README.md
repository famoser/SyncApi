# SyncApi
C#: [![Build status](https://ci.appveyor.com/api/projects/status/un5aq493586670k3?svg=true)](https://ci.appveyor.com/project/famoser/syncapi)
php: [![Build Status](https://travis-ci.org/famoser/SyncApi.svg?branch=master)](https://travis-ci.org/famoser/SyncApi)
[![Issue Count](https://codeclimate.com/github/famoser/SyncApi/badges/issue_count.svg)](https://codeclimate.com/github/famoser/SyncApi)
[![Code Climate](https://codeclimate.com/github/famoser/SyncApi/badges/gpa.svg)](https://codeclimate.com/github/famoser/SyncApi)
[![Test Coverage](https://codeclimate.com/github/famoser/SyncApi/badges/coverage.svg)](https://codeclimate.com/github/famoser/SyncApi/coverage)

a nuget portable library to sync entities in a very convenient way.

This library manages the synchronization between multiple installation of an application of the same user. 
You may save any entity you want, and it will be synced typesafe. Included is some sort of version control (you will be able to access older versions of an entity).

It uses the roaming storage provided by UWP to save user information, all actual data is synced over a php api.
The library is heavily customizable, while allowing an easy and straightforward approach for simple use cases.

Code example:

    //construct the api helper (storage service is implementated in Famoser.UniversalEssentials for UWP)
    IStorageService ss = new StorageService();
    var helper = new SyncApiHelper(ss, "my_application_name", "https://api.mywebpage.ch");

    //get my repository
    var repo = helper.ResolveRepository<NoteModel>();

    //save my model
    await repo.SaveAsync(new NoteModel { Content = "Hallo Welt!" });

    //retrieve it later on
    ObservableCollection<NoteModel> coll = await repo.GetAllAsync();
