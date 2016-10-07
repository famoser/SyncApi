using System;
using System.Threading.Tasks;

namespace Famoser.SyncApi.Services.Interfaces
{
    public interface IApiConfigurationService
    {
        Uri GetApiUri();
        Task<object> GetDeviceObjectAsync();
        Task<object> GetUserObjectAsync(); 
        string GetFileName(string proposedFilename, Type objectType = null);
    }
}
