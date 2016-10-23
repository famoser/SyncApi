﻿using System;
using System.Threading.Tasks;
using Famoser.SyncApi.Api.Configuration;
using Famoser.SyncApi.Models;
using Famoser.SyncApi.Services.Interfaces;

#pragma warning disable 1998
namespace Famoser.SyncApi.Services
{
    public class ApiConfigurationService : IApiConfigurationService
    {
        private readonly string _applicationId;
        private readonly Uri _baseUri;
        public ApiConfigurationService(string applicationId, string baseUri = "https://public.syncapi.famoser.ch")
        {
            _applicationId = applicationId;
            _baseUri = new Uri(baseUri);
        }

        public ApiInformationEntity GetApiInformations()
        {
            return new ApiInformationEntity()
            {
                Uri = _baseUri,
                Modulo = 10000019,
                ApplicationSeed = 3102,
                ApplicationId = _applicationId
            };
        }

        public async Task<TUser> GetUserObjectAsync<TUser>() where TUser : class
        {
            if (typeof(TUser) == typeof(UserModel))
                return new UserModel() as TUser;
            return default(TUser);
        }

        public async Task<TDevice> GetDeviceObjectAsync<TDevice>() where TDevice : class
        {
            if (typeof(TDevice) == typeof(DeviceModel))
                return new DeviceModel() as TDevice;
            return default(TDevice);
        }

        public async Task<TCollection> GetCollectionObjectAsync<TCollection>() where TCollection : class
        {
            if (typeof(TCollection) == typeof(UserModel))
                return new CollectionModel() as TCollection;
            return default(TCollection);
        }

        public string GetFileName(string proposedFilename, Type objectType = null)
        {
            return proposedFilename;
        }
    }
}
