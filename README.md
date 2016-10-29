# SyncApi
[![Build Status](https://travis-ci.org/famoser/SyncApi.svg?branch=master)](https://travis-ci.org/famoser/SyncApi)
[![Build status](https://ci.appveyor.com/api/projects/status/un5aq493586670k3?svg=true)](https://ci.appveyor.com/project/famoser/syncapi)
[![Issue Count](https://codeclimate.com/github/famoser/SyncApi/badges/issue_count.svg)](https://codeclimate.com/github/famoser/SyncApi)

a nuget portable library to sync models in a very convenient way.

This library manages the synchronization between multiple installation of an application of the same user. 
You may save any entity you want, and it will be synced typesafe. Also included is some sort of version control, and history for each entity.

It uses the roaming storage provided by UWP to save user information, all actual data is synced over a php api.
The library is heavily customizable through an elaborate IoC concept, while allowing an easy and straightforward approach for simple use cases.
