using System;
using System.Threading.Tasks;
using Famoser.SyncApi.Models.Interfaces;

namespace Famoser.SyncApi.Services.Interfaces
{
    public interface IApiConfigurationService
    {
        Uri GetApiUri();
        Task<IDeviceModel> GetDeviceObjectAsync();
        Task<IUserModel> GetUserObjectAsync(); 
        string GetFileName(string proposedFilename, Type objectType = null);
    }
}
