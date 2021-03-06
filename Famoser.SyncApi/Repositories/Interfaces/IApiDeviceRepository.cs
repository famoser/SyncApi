﻿using System.Collections.ObjectModel;
using System.Threading.Tasks;
using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Repositories.Interfaces.Base;
using Famoser.SyncApi.Services.Interfaces.Authentication;

namespace Famoser.SyncApi.Repositories.Interfaces
{
    public interface IApiDeviceRepository<TDevice> : IPersistentRespository<TDevice>, IApiDeviceAuthenticationService
        where TDevice : IDeviceModel
    {
        /// <summary>
        /// get all devices from an user
        /// return the collection early, and fill it in the background with the data
        /// </summary>
        /// <returns></returns>
        ObservableCollection<TDevice> GetAllLazy();
        Task<ObservableCollection<TDevice>> GetAllAsync();
        Task<bool> SyncDevicesAsync();

        Task<bool> UnAuthenticateAsync(TDevice device);
        Task<bool> AuthenticateAsync(TDevice device);

        Task<string> CreateNewAuthenticationCodeAsync();
        Task<bool> TryUseAuthenticationCodeAsync(string authenticationCode);
    }
}
