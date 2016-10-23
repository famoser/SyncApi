using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Models;
using Famoser.SyncApi.Repositories;
using Famoser.SyncApi.Services;
using Famoser.SyncApi.Services.Interfaces;
using Famoser.SyncApi.Services.Interfaces.Authentication;
using GalaSoft.MvvmLight.Ioc;

namespace Famoser.SyncApi.NUnitTests.Helpers
{
    public class IocHelper
    {
        public static void InitializeContainer()
        {
            SimpleIoc.Default.Register<IApiConfigurationService>(() => new ApiConfigurationService("NUnitTests", "https://test.syncapi.famoser.ch"));
            SimpleIoc.Default.Register<IApiStorageService, ApiStorageService>();
            var userRep = SimpleIoc.Default.GetInstanceWithoutCaching<ApiUserRepository<UserModel>>();
            var deviceRepo = SimpleIoc.Default.GetInstanceWithoutCaching<ApiDeviceRepository<DeviceModel>>();
            var aus = new ApiAuthenticationService(SimpleIoc.Default.GetInstance<IApiConfigurationService>(), userRep, deviceRepo);
            SimpleIoc.Default.Register<IApiAuthenticationService>(() => aus);
        }
    }
}
