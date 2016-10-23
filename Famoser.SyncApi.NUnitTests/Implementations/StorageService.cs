using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.FrameworkEssentials.Services.Interfaces;
using Famoser.SyncApi.Services.Interfaces;

#pragma warning disable 1998
namespace Famoser.SyncApi.NUnitTests.Implementations
{
    public class StorageService : IStorageService
    {
        private readonly Dictionary<string, string> _cachedFiles = new Dictionary<string, string>();
        public async Task<string> GetCachedTextFileAsync(string filePath)
        {
            return _cachedFiles[filePath];
        }

        public async Task<bool> SetCachedTextFileAsync(string filePath, string content)
        {
            _cachedFiles[filePath] = content;
            return true;
        }

        public Task<byte[]> GetCachedFileAsync(string filePath)
        {
            throw new NotImplementedException();
        }

        public async Task<bool> SetCachedFileAsync(string filePath, byte[] content)
        {
            throw new NotImplementedException();
        }

        public async Task<bool> DeleteCachedFileAsync(string filePath)
        {
            throw new NotImplementedException();
        }

        private readonly Dictionary<string, string> _roamingFiles = new Dictionary<string, string>();
        public async Task<string> GetRoamingTextFileAsync(string filePath)
        {
            return _roamingFiles[filePath];
        }

        public async Task<bool> SetRoamingTextFileAsync(string filePath, string content)
        {
            _roamingFiles[filePath] = content;
            return true;
        }

        public Task<byte[]> GetRoamingFileAsync(string filePath)
        {
            throw new NotImplementedException();
        }

        public async Task<bool> SetRoamingFileAsync(string filePath, byte[] content)
        {
            throw new NotImplementedException();
        }

        public async Task<bool> DeleteRoamingFileAsync(string filePath)
        {
            throw new NotImplementedException();
        }

        public async Task<string> GetAssetTextFileAsync(string filePath)
        {
            throw new NotImplementedException();
        }

        public Task<byte[]> GetAssetFileAsync(string filePath)
        {
            throw new NotImplementedException();
        }
    }
}
