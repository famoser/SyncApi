using System;
using System.Threading.Tasks;

namespace Famoser.SyncApi.Interfaces
{
    public interface IApiConfigurationService
    {
        Task<Uri> GetApiUri();
        Task<object> GetDeviceObjectAsync();
        Task<object> GetUserObjectAsync(); 
        string GetFileName(string proposedFilename, Type objectType = null);
    }
}
