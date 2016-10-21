using System;
using System.Threading.Tasks;
using Famoser.SyncApi.Api.Configuration;

namespace Famoser.SyncApi.Services.Interfaces
{
    public interface IApiConfigurationService
    {
        ApiInformationEntity GetApiInformations();
        Task<TUser> GetUserObjectAsync<TUser>();
        Task<TDevice> GetDeviceObjectAsync<TDevice>();
        Task<TCollection> GetCollectionObjectAsync<TCollection>();
        string GetFileName(string proposedFilename, Type objectType = null);
    }
}
