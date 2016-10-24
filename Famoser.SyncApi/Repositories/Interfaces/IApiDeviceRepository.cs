using System.Collections.ObjectModel;
using System.Threading.Tasks;
using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Repositories.Interfaces.Base;

namespace Famoser.SyncApi.Repositories.Interfaces
{
    public interface IApiDeviceRepository<TDevice> : IPersistentRespository<TDevice>
        where TDevice : IDeviceModel
    {
        ObservableCollection<TDevice> GetAllLazy();
        Task<ObservableCollection<TDevice>> GetAllAsync();
        Task<bool> SyncDevicesAsync();

        Task<bool> UnAuthenticateAsync(TDevice device);
        Task<bool> AuthenticateAsync(TDevice device);

        Task<string> CreateNewAuthenticationCodeAsync();
        Task<bool> TryUseAuthenticationCodeAsync(string authenticationCode);
    }
}
