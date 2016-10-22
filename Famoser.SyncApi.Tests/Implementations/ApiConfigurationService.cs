using System;
using System.Threading.Tasks;
using Famoser.SyncApi.Api.Configuration;
using Famoser.SyncApi.Models;
using Famoser.SyncApi.Services.Interfaces;

#pragma warning disable 1998
namespace Famoser.SyncApi.Tests.Implementations
{
    public class ApiConfigurationService : IApiConfigurationService
    {
        public ApiInformationEntity GetApiInformations()
        {
            return new ApiInformationEntity()
            {
                Uri = new Uri("https://syncapi.famoser.ch"),
                Modulo = 10000019,
                Seed = 3102
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
