using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Linq;
using Famoser.FrameworkEssentials.Services.Interfaces;
using Famoser.SyncApi.Events;
using Famoser.SyncApi.Helpers;
using Famoser.SyncApi.Models;
using Famoser.SyncApi.Services;
using Famoser.SyncApi.Services.Interfaces;
using Famoser.SyncApi.UnitTests.Implementations;
using Microsoft.VisualStudio.TestTools.UnitTesting;

namespace Famoser.SyncApi.UnitTests.Helpers
{
    public class TestHelper
    {
        private static string _applicationId = "test_appl";
        private static long _applicationSeed = 1341451215173;
        private static string _testUri = "https://dev.syncapi.famoser.ch/";
        //private static string _testUri = "http://localhost/";

        public static string TestUri => _testUri;
        public List<RequestEventArgs> FailedRequestEventArgs { get; } = new List<RequestEventArgs>();
        public List<RequestEventArgs> SuccessfulRequestEventArgs { get; } = new List<RequestEventArgs>();
        public ObservableCollection<SyncActionInformation> SyncActionInformations { get; private set; }


        private IApiTraceService _apiTraceService;
        public IApiTraceService ApiTraceService
        {
            get
            {
                if (_apiTraceService != null)
                    return _apiTraceService;

                var trace = new ApiTraceService();

                trace.RequestFailed += (sender, args) => FailedRequestEventArgs.Add(args);
                trace.RequestSuccessful += (sender, args) => SuccessfulRequestEventArgs.Add(args);
                SyncActionInformations = trace.SyncActionInformations;

                _apiTraceService = trace;
                return _apiTraceService;
            }
            set { _apiTraceService = value; }
        }

        private IStorageService _storageService;
        public IStorageService StorageService
        {
            get { return _storageService ?? (_storageService = new StorageService()); }
            set { _storageService = value; }
        }

        private Func<bool> _canUseWebConnectionFunc;
        public Func<bool> CanUserWebConnectionFunc
        {
            private get { return _canUseWebConnectionFunc ?? (_canUseWebConnectionFunc = () => true); }
           set { _canUseWebConnectionFunc = value; }
        }


        private SyncApiHelper _syncApiHelper;
        public SyncApiHelper SyncApiHelper
        {
            get
            {
                return _syncApiHelper ?? (_syncApiHelper =
                    new SyncApiHelper(StorageService, _applicationId, _testUri)
                    {
                        ApiConfigurationService =
                            new ApiConfigurationService(_applicationId, _testUri, CanUserWebConnectionFunc),
                        ApiTraceService = ApiTraceService
                    });
            }
            set { _syncApiHelper = value; }
        }

        public void AssertNoFailedRequests()
        {
            Assert.IsFalse(FailedRequestEventArgs.Any(), "some requests failed");
        }

        public void AssertNoErrors()
        {
            AssertNoFailedRequests();
        }
    }
}
